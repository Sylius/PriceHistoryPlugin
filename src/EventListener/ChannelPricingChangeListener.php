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

namespace Sylius\PriceHistoryPlugin\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Checker\ProductVariantVisibilityCheckerInterface;
use Sylius\PriceHistoryPlugin\Model\ChannelPricingLogEntry;
use Webmozart\Assert\Assert;

final class ChannelPricingChangeListener
{
    private const SUPPORTED_FIELDS = ['price', 'originalPrice'];

    public function __construct(
        private ProductVariantVisibilityCheckerInterface $productVariantVisibilityChecker,
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->createLogEntryOnUpdate($entityManager, $unitOfWork);
        $this->createLogEntryOnCreate($entityManager, $unitOfWork);

        $unitOfWork->computeChangeSets();
    }

    private function createLogEntryOnUpdate(EntityManagerInterface $entityManager, UnitOfWork $unitOfWork): void
    {
        /** @var ChannelPricingInterface $entity */
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (!$this->supports($entity) || !$this->isPriceChanged($unitOfWork, $entity)) {
                continue;
            }

            $logEntry = $this->createLogEntry($entity);
            $entityManager->persist($logEntry);
        }
    }

    private function createLogEntryOnCreate(EntityManagerInterface $entityManager, UnitOfWork $unitOfWork): void
    {
        /** @var ChannelPricingInterface $entity */
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if (!$this->supports($entity)) {
                continue;
            }

            $logEntry = $this->createLogEntry($entity);
            $entityManager->persist($logEntry);
        }
    }

    private function createLogEntry(ChannelPricingInterface $model): ChannelPricingLogEntry
    {
        Assert::notNull($price = $model->getPrice());

        $channel = $this->channelRepository->findOneByCode($model->getChannelCode());
        dd($channel);
        $isChangeVisible = $this->productVariantVisibilityChecker->isVisibleInChannel($model->getProductVariant(), $channel);

        return new ChannelPricingLogEntry($model, $price, $model->getOriginalPrice(), $isChangeVisible);
    }

    private function isPriceChanged(UnitOfWork $unitOfWork, ChannelPricingInterface $channelPricing): bool
    {
        $changedFields = array_keys($unitOfWork->getEntityChangeSet($channelPricing));

        return [] !== array_intersect($changedFields, self::SUPPORTED_FIELDS);
    }

    private function supports(object $model): bool
    {
        return $model instanceof ChannelPricingInterface;
    }
}
