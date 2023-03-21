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

namespace Sylius\PriceHistoryPlugin\Application\CommandHandler;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Command\ApplyLowestPriceOnChannelPricings;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ApplyLowestPriceOnChannelPricingsHandler
{
    public function __construct(
        private ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        private RepositoryInterface $channelPricingRepository,
    ) {
    }

    public function __invoke(ApplyLowestPriceOnChannelPricings $applyLowestPriceOnChannelPricings): void
    {
        /** @var ChannelPricingInterface[] $channelPricings */
        $channelPricings = $this->channelPricingRepository->findBy(
            ['id' => $applyLowestPriceOnChannelPricings->channelPricingIds],
        );

        foreach ($channelPricings as $channelPricing) {
            $this->productLowestPriceBeforeDiscountProcessor->process($channelPricing);
        }
    }
}
