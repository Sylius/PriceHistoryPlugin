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

namespace Sylius\PriceHistoryPlugin\Infrastructure\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange\OnEntityChangeInterface;

final class OnFlushEntityChangeListener
{
    public function __construct(private iterable $onEntityChange)
    {
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $scheduledEntities = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates(),
        );

        foreach ($scheduledEntities as $entity) {
            /** @var OnEntityChangeInterface $onEntityChange */
            foreach ($this->onEntityChange as $onEntityChange) {
                $supportedEntity = $onEntityChange->getSupportedEntity();

                if (
                    !$entity instanceof $supportedEntity ||
                    !$this->isEntityChanged($unitOfWork, $entity, $onEntityChange->getSupportedFields())
                ) {
                    continue;
                }

                $onEntityChange->onChange($entity);
            }
        }

        $unitOfWork->computeChangeSets();
    }

    private function isEntityChanged(UnitOfWork $unitOfWork, object $entity, array $supportedFields): bool
    {
        $changedFields = array_keys($unitOfWork->getEntityChangeSet($entity));

        return [] !== array_intersect($changedFields, $supportedFields);
    }
}
