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

namespace Sylius\PriceHistoryPlugin\Application\Calculator;

use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Checker\ProductVariantLowestPriceDisplayCheckerInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Webmozart\Assert\Assert;

final class ProductVariantLowestPriceCalculator implements ProductVariantLowestPriceCalculatorInterface
{
    public function __construct(private ProductVariantLowestPriceDisplayCheckerInterface $productVariantLowestPriceDisplayChecker)
    {
    }

    public function calculateLowestPriceBeforeDiscount(ProductVariantInterface $productVariant, array $context): ?int
    {
        Assert::keyExists($context, 'channel');
        $channel = $context['channel'];
        Assert::isInstanceOf($channel, ChannelInterface::class);

        /** @var ChannelPricingInterface|null $channelPricing */
        $channelPricing = $productVariant->getChannelPricingForChannel($channel);
        if (null === $channelPricing) {
            throw MissingChannelConfigurationException::createForProductVariantChannelPricing($productVariant, $channel);
        }

        if (!$this->productVariantLowestPriceDisplayChecker->isLowestPriceDisplayable($productVariant, $context)) {
            return null;
        }

        return $channelPricing->getLowestPriceBeforeDiscount();
    }
}
