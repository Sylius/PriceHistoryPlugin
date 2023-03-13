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

namespace Sylius\PriceHistoryPlugin\Application\Processor;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Domain\Repository\ChannelPricingLogEntryRepositoryInterface;
use Webmozart\Assert\Assert;

final class ProductLowestPriceBeforeDiscountProcessor implements ProductLowestPriceBeforeDiscountProcessorInterface
{
    public function __construct(
        private ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function process(ChannelPricingInterface $channelPricing): void
    {
        if (!$this->isPromotionApplied($channelPricing)) {
            $channelPricing->setLowestPriceBeforeDiscount(null);

            return;
        }

        $channelCode = $channelPricing->getChannelCode();
        Assert::string($channelCode);

        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($channelCode);

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $lowestPriceBeforeDiscount = $this->channelPricingLogEntryRepository->findLowestPricesBeforeDiscount(
            $channelPricing,
            $channel->getLowestPriceForDiscountedProductsCheckingPeriod(),
        );

        $channelPricing->setLowestPriceBeforeDiscount($lowestPriceBeforeDiscount);
    }

    private function isPromotionApplied(ChannelPricingInterface $channelPricing): bool
    {
        return
            $channelPricing->getOriginalPrice() !== null &&
            $channelPricing->getPrice() < $channelPricing->getOriginalPrice()
        ;
    }
}
