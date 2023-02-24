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

namespace Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;

final class ProcessLowestPriceOnCheckingPeriodChange implements OnEntityChangeInterface
{
    public function __construct(
        private ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        private RepositoryInterface $channelPricingRepository,
    ) {
    }

    /**
     * @param ChannelInterface $entity
     */
    public function onChange(object $entity): void
    {
        $channelPricings = $this->channelPricingRepository->findBy(['channelCode' => $entity->getCode()]);

        foreach ($channelPricings as $channelPricing) {
            $this->productLowestPriceBeforeDiscountProcessor->process($channelPricing);
        }
    }

    public function getSupportedEntity(): string
    {
        return ChannelInterface::class;
    }

    public function getSupportedFields(): array
    {
        return ['lowestPriceForDiscountedProductsCheckingPeriod'];
    }
}
