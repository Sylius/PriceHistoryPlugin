<?php

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
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange\OnEntityChangeInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\OnFlushEntityChangeListener;

final class OnFlushEntityChangeListenerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->beConstructedWith([]);

        $this->shouldHaveType(OnFlushEntityChangeListener::class);
    }

    function it_process_entities_on_flush_event(
        OnEntityChangeInterface $firstOnFlushEntityChanged,
        OnEntityChangeInterface $secondOnFlushEntityChanged,
        OnFlushEventArgs        $eventArgs,
        EntityManagerInterface  $entityManager,
        ChannelPricingInterface $firstEntity,
        ProductInterface        $secondEntity,
        ProductInterface        $thirdEntity,
        ChannelPricingInterface $fourthEntity,
        OrderInterface          $fifthEntity,
    ): void {
        $this->beConstructedWith([$firstOnFlushEntityChanged, $secondOnFlushEntityChanged]);

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
        );

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $firstOnFlushEntityChanged->getSupportedEntity()->willReturn(ChannelPricingInterface::class);
        $firstOnFlushEntityChanged->getSupportedFields()->willReturn(['price', 'originalPrice']);
        $firstOnFlushEntityChanged->onChange($firstEntity)->shouldBeCalled();
        $firstOnFlushEntityChanged->onChange($secondEntity)->shouldNotBeCalled();
        $firstOnFlushEntityChanged->onChange($thirdEntity)->shouldNotBeCalled();
        $firstOnFlushEntityChanged->onChange($fourthEntity)->shouldBeCalled();
        $firstOnFlushEntityChanged->onChange($fifthEntity)->shouldNotBeCalled();

        $secondOnFlushEntityChanged->getSupportedEntity()->willReturn(ProductInterface::class);
        $secondOnFlushEntityChanged->getSupportedFields()->willReturn(['enabled']);
        $secondOnFlushEntityChanged->onChange($firstEntity)->shouldNotBeCalled();
        $secondOnFlushEntityChanged->onChange($secondEntity)->shouldBeCalled();
        $secondOnFlushEntityChanged->onChange($thirdEntity)->shouldBeCalled();
        $secondOnFlushEntityChanged->onChange($fourthEntity)->shouldNotBeCalled();
        $secondOnFlushEntityChanged->onChange($fifthEntity)->shouldNotBeCalled();

        $this->onFlush($eventArgs);
    }

    private function getUnitOfWorkMock(
        array $createdEntitiesWithChanges,
        array $updatedEntitiesWithChanges,
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
                $createdEntitiesWithChanges
            ))
        ;

        $unitOfWork
            ->shouldReceive('getScheduledEntityUpdates')
            ->withNoArgs()
            ->andReturn(array_map(
                fn (array $entityWithChanges) => $entityWithChanges[0],
                $updatedEntitiesWithChanges
            ))
        ;

        $unitOfWork->shouldReceive('computeChangeSets')->withNoArgs()->once();

        return $unitOfWork;
    }
}
