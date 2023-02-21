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

namespace spec\Sylius\PriceHistoryPlugin\Application\Provider;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Provider\ProductVariantPriceProviderInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ProductVariantPriceProviderSpec extends ObjectBehavior
{
    function it_implements_product_variant_price_provider_interface(): void
    {
        $this->shouldImplement(ProductVariantPriceProviderInterface::class);
    }

    function it_returns_the_lowest_price_before_discount(
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn($channelPricing);
        $channelPricing->getLowestPriceBeforeDiscount()->willReturn(2100);

        $this->getLowestPriceBeforeDiscount($productVariant, $channel)->shouldReturn(2100);
    }

    function it_throws_a_channel_not_defined_exception_if_there_is_no_channel_pricing_when_providing_the_lowest_price_before_discount(
        ChannelInterface $channel,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn(null);
        $productVariant->getDescriptor()->willReturn('Red variant (RED_VARIANT)');

        $this
            ->shouldThrow(MissingChannelConfigurationException::class)
            ->during('getLowestPriceBeforeDiscount', [$productVariant, $channel])
        ;
    }
}
