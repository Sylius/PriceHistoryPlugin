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

namespace Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Webmozart\Assert\Assert;

final class ProcessLowestPriceOnCheckingPeriodChangeObserver implements EntityObserverInterface
{
    public function __construct(
        private ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        private RepositoryInterface $channelPricingRepository,
    ) {
    }

    public function onChange(object $entity): void
    {
        Assert::isInstanceOf($entity, ChannelInterface::class);

        /** @var ChannelPricingInterface[] $channelPricings */
        $channelPricings = $this->channelPricingRepository->findBy(['channelCode' => $entity->getCode()]);

        foreach ($channelPricings as $channelPricing) {
            $this->productLowestPriceBeforeDiscountProcessor->process($channelPricing);
        }
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof ChannelInterface;
    }

    public function observedFields(): array
    {
        return ['lowestPriceForDiscountedProductsCheckingPeriod'];
    }
}
