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
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver\EntityObserverInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\OnFlushEntityObserverListener;

final class OnFlushEntityObserverListenerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->beConstructedWith([]);

        $this->shouldHaveType(OnFlushEntityObserverListener::class);
    }

    function it_process_entities_on_flush_event(
        EntityObserverInterface $firstEntityObserver,
        EntityObserverInterface $secondEntityObserver,
        OnFlushEventArgs $eventArgs,
        EntityManagerInterface $entityManager,
        ChannelPricingInterface $firstEntity,
        ProductInterface $secondEntity,
        ProductInterface $thirdEntity,
        ChannelPricingInterface $fourthEntity,
        OrderInterface $fifthEntity,
    ): void {
        $this->beConstructedWith([$firstEntityObserver, $secondEntityObserver]);

        $eventArgs->getObjectManager()->willReturn($entityManager);

        $unitOfWork = $this->getUnitOfWorkMock(
            [
                [$firstEntity->getWrappedObject(), [
                    'price' => [null, 1000],
                ]],
                [$secondEntity->getWrappedObject(), [
                    'code' => [null, 'SWAN-001'],
                    'enabled' => [null, false],
                ]],
            ],
            [
                [$thirdEntity->getWrappedObject(), [
                    'enabled' => [true, false],
                ]],
                [$fourthEntity->getWrappedObject(), [
                    'price' => [2000, 1000],
                    'originalPrice' => [null, 2000],
                ]],
                [$fifthEntity->getWrappedObject(), [
                    'notes' => ['swam', 'swan'],
                ]],
            ],
            true,
        );

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $firstEntityObserver->supports($firstEntity)->willReturn(true);
        $firstEntityObserver->supports($secondEntity)->willReturn(false);
        $firstEntityObserver->supports($thirdEntity)->willReturn(false);
        $firstEntityObserver->supports($fourthEntity)->willReturn(true);
        $firstEntityObserver->supports($fifthEntity)->willReturn(false);
        $firstEntityObserver->observedFields()->willReturn(['price', 'originalPrice']);
        $firstEntityObserver->onChange($firstEntity)->shouldBeCalled();
        $firstEntityObserver->onChange($secondEntity)->shouldNotBeCalled();
        $firstEntityObserver->onChange($thirdEntity)->shouldNotBeCalled();
        $firstEntityObserver->onChange($fourthEntity)->shouldBeCalled();
        $firstEntityObserver->onChange($fifthEntity)->shouldNotBeCalled();

        $secondEntityObserver->supports($firstEntity)->willReturn(false);
        $secondEntityObserver->supports($secondEntity)->willReturn(true);
        $secondEntityObserver->supports($thirdEntity)->willReturn(true);
        $secondEntityObserver->supports($fourthEntity)->willReturn(false);
        $secondEntityObserver->supports($fifthEntity)->willReturn(false);
        $secondEntityObserver->observedFields()->willReturn(['enabled']);
        $secondEntityObserver->onChange($firstEntity)->shouldNotBeCalled();
        $secondEntityObserver->onChange($secondEntity)->shouldBeCalled();
        $secondEntityObserver->onChange($thirdEntity)->shouldBeCalled();
        $secondEntityObserver->onChange($fourthEntity)->shouldNotBeCalled();
        $secondEntityObserver->onChange($fifthEntity)->shouldNotBeCalled();

        $this->onFlush($eventArgs);
    }

    public function it_does_not_process_entities_on_flush_event_if_there_are_no_changes_on_entities(
        EntityObserverInterface $firstEntityObserver,
        EntityObserverInterface $secondEntityObserver,
        OnFlushEventArgs $eventArgs,
        EntityManagerInterface $entityManager,
        ProductInterface $firstEntity,
        ChannelPricingInterface $secondEntity,
        OrderInterface $thirdEntity,
    ): void {
        $this->beConstructedWith([$firstEntityObserver, $secondEntityObserver]);

        $eventArgs->getObjectManager()->willReturn($entityManager);

        $unitOfWork = $this->getUnitOfWorkMock(
            [],
            [
                [$firstEntity->getWrappedObject(), [
                    'name' => ['swam', 'swan'],
                ]],
                [$secondEntity->getWrappedObject(), []],
                [$thirdEntity->getWrappedObject(), [
                    'notes' => ['swam', 'swan'],
                ]],
            ],
            false,
        );

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $firstEntityObserver->supports($firstEntity)->willReturn(false);
        $firstEntityObserver->supports($secondEntity)->willReturn(true);
        $firstEntityObserver->supports($thirdEntity)->willReturn(false);
        $firstEntityObserver->observedFields()->willReturn(['price', 'originalPrice']);
        $firstEntityObserver->onChange($firstEntity)->shouldNotBeCalled();
        $firstEntityObserver->onChange($secondEntity)->shouldNotBeCalled();
        $firstEntityObserver->onChange($thirdEntity)->shouldNotBeCalled();

        $secondEntityObserver->supports($firstEntity)->willReturn(true);
        $secondEntityObserver->supports($secondEntity)->willReturn(false);
        $secondEntityObserver->supports($thirdEntity)->willReturn(false);
        $secondEntityObserver->observedFields()->willReturn(['enabled']);
        $secondEntityObserver->onChange($firstEntity)->shouldNotBeCalled();
        $secondEntityObserver->onChange($secondEntity)->shouldNotBeCalled();
        $secondEntityObserver->onChange($thirdEntity)->shouldNotBeCalled();

        $this->onFlush($eventArgs);
    }

    public function it_throws_an_error_if_at_least_one_entity_observer_does_not_implement_entity_observer_interface(): void
    {
        $this->beConstructedWith([new \stdClass()]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    private function getUnitOfWorkMock(
        array $createdEntitiesWithChanges,
        array $updatedEntitiesWithChanges,
        bool $shouldComputeChangeSets,
    ): UnitOfWork {
        /** @var UnitOfWork|MockInterface $unitOfWork */
        $unitOfWork = Mockery::mock(UnitOfWork::class);

        foreach (array_merge($createdEntitiesWithChanges, $updatedEntitiesWithChanges) as $entityWithChanges) {
            $unitOfWork
                ->shouldReceive('getEntityChangeSet')
                ->withArgs([$entityWithChanges[0]])
                ->andReturn($entityWithChanges[1])
                ->once()
            ;
        }

        $unitOfWork
            ->shouldReceive('getScheduledEntityInsertions')
            ->withNoArgs()
            ->andReturn(array_map(
                fn (array $entityWithChanges) => $entityWithChanges[0],
                $createdEntitiesWithChanges,
            ))
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityUpdates')
            ->withNoArgs()
            ->andReturn(array_map(
                fn (array $entityWithChanges) => $entityWithChanges[0],
                $updatedEntitiesWithChanges,
            ))
        ;

        if ($shouldComputeChangeSets) {
            $unitOfWork->shouldReceive('computeChangeSets')->withNoArgs()->once();
        } else {
            $unitOfWork->shouldReceive('computeChangeSets')->withNoArgs()->never();
        }

        return $unitOfWork;
    }
}
