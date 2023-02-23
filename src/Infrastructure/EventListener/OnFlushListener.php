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
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Logger\PriceChangeLoggerInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;

final class OnFlushListener
{
    public function __construct(
        private PriceChangeLoggerInterface $priceChangeLogger,
        private ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        private RepositoryInterface $channelPricingRepository,
    ) {
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() + $unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->createLogEntryOnPriceChange($unitOfWork, $entity);
            $this->processLowestPriceOnCheckingPeriodChange($unitOfWork, $entity);
        }

        $unitOfWork->computeChangeSets();
    }

    private function createLogEntryOnPriceChange(UnitOfWork $unitOfWork, object $entity): void
    {
        if (
            !$entity instanceof ChannelPricingInterface
            || !$this->isEntityChanged($unitOfWork, $entity, ['price', 'originalPrice'])
        ) {
            return;
        }

        $this->priceChangeLogger->log($entity);
    }

    private function processLowestPriceOnCheckingPeriodChange(UnitOfWork $unitOfWork, object $entity): void
    {
        if (!$entity instanceof ChannelInterface || !$this->isEntityChanged($unitOfWork, $entity, ['lowestPriceForDiscountedProductsCheckingPeriod'])) {
            return;
        }

        $channelPricings = $this->channelPricingRepository->findBy(['channelCode' => $entity->getCode()]);

        foreach ($channelPricings as $channelPricing) {
            $this->productLowestPriceBeforeDiscountProcessor->process($channelPricing);
        }
    }

    private function isEntityChanged(UnitOfWork $unitOfWork, object $entity, array $supportedFields): bool
    {
        $changedFields = array_keys($unitOfWork->getEntityChangeSet($entity));

        return [] !== array_intersect($changedFields, $supportedFields);
    }
}
