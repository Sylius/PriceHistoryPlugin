<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Mockery;
use Mockery\MockInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingLogEntryInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\ChannelPricingChangeListener;

class ChannelPricingChangeListenerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(ChannelPricingChangeListener::class);
    }

    function it_creates_channel_pricing_log_entries_for_updated_and_newly_created_channel_pricings_only(
        OnFlushEventArgs $eventArgs,
        EntityManagerInterface $entityManager,
        ChannelPricingInterface $newChannelPricing,
        ProductInterface $newProduct,
        ChannelPricingInterface $updatedChannelPricing,
        ProductInterface $updatedProduct,
    ): void {
        $eventArgs->getObjectManager()->willReturn($entityManager);

        $updatedChannelPricing->getPrice()->willReturn(2000);
        $updatedChannelPricing->getOriginalPrice()->willReturn(3000);

        $newChannelPricing->getPrice()->willReturn(5000);
        $newChannelPricing->getOriginalPrice()->willReturn(null);

        /** @var UnitOfWork|MockInterface $unitOfWork */
        $unitOfWork = Mockery::mock(UnitOfWork::class);

        $unitOfWork
            ->shouldReceive('getEntityChangeSet')
            ->withArgs([$updatedChannelPricing->getWrappedObject()])
            ->andReturn([
                'price' => [1000, 2000],
                'originalPrice' => [null, 3000],
            ])
            ->once()
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityUpdates')
            ->withNoArgs()
            ->andReturn([
                $updatedChannelPricing->getWrappedObject(),
                $updatedProduct->getWrappedObject()
            ])
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityInsertions')
            ->withNoArgs()
            ->andReturn([
                $newChannelPricing->getWrappedObject(),
                $newProduct->getWrappedObject()
            ])
        ;

        $unitOfWork->shouldReceive('computeChangeSets')->withNoArgs()->once();

        $entityManager
            ->persist(Argument::type(ChannelPricingLogEntryInterface::class))
            ->shouldBeCalledTimes(2)    // once for updated channel pricing and once for new channel pricing
        ;
        $entityManager->persist(Argument::type(ProductInterface::class))->shouldNotBeCalled();

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $this->onFlush($eventArgs);
    }

    function it_does_not_create_channel_pricing_log_entry_if_price_and_original_price_have_not_changed(
        OnFlushEventArgs $eventArgs,
        EntityManagerInterface $entityManager,
        ChannelPricingInterface $updatedChannelPricing,
    ): void {
        $eventArgs->getObjectManager()->willReturn($entityManager);

        $updatedChannelPricing->getPrice()->shouldNotBeCalled();
        $updatedChannelPricing->getOriginalPrice()->shouldNotBeCalled();

        /** @var UnitOfWork|MockInterface $unitOfWork */
        $unitOfWork = Mockery::mock(UnitOfWork::class);

        $unitOfWork
            ->shouldReceive('getEntityChangeSet')
            ->withArgs([$updatedChannelPricing->getWrappedObject()])
            ->andReturn(['minimumPrice' => [1000, 2000]])
            ->once()
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityUpdates')
            ->withNoArgs()
            ->andReturn([$updatedChannelPricing->getWrappedObject()])
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityInsertions')
            ->withNoArgs()
            ->andReturn([])
        ;

        $unitOfWork->shouldReceive('computeChangeSets')->withNoArgs()->once();
        $entityManager->persist(Argument::type(ChannelPricingLogEntryInterface::class))->shouldNotBeCalled();

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $this->onFlush($eventArgs);
    }

    function it_creates_channel_pricing_log_entry_if_at_least_one_supported_field_has_changed(
        OnFlushEventArgs $eventArgs,
        EntityManagerInterface $entityManager,
        ChannelPricingInterface $updatedChannelPricing,
    ): void {
        $eventArgs->getObjectManager()->willReturn($entityManager);

        $updatedChannelPricing->getPrice()->willReturn(2000);
        $updatedChannelPricing->getOriginalPrice()->willReturn(3000);

        /** @var UnitOfWork|MockInterface $unitOfWork */
        $unitOfWork = Mockery::mock(UnitOfWork::class);

        $unitOfWork
            ->shouldReceive('getEntityChangeSet')
            ->withArgs([$updatedChannelPricing->getWrappedObject()])
            ->andReturn(['price' => [1000, 2000]])
            ->once()
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityUpdates')
            ->withNoArgs()
            ->andReturn([$updatedChannelPricing->getWrappedObject()])
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityInsertions')
            ->withNoArgs()
            ->andReturn([])
        ;

        $unitOfWork->shouldReceive('computeChangeSets')->withNoArgs()->once();
        $entityManager->persist(Argument::type(ChannelPricingLogEntryInterface::class))->shouldBeCalledOnce();

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $this->onFlush($eventArgs);
    }
}
