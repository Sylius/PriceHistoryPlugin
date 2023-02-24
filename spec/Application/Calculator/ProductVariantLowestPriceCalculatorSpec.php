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

namespace spec\Sylius\PriceHistoryPlugin\Application\Calculator;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Calculator\ProductVariantLowestPriceCalculatorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ProductVariantLowestPriceCalculatorSpec extends ObjectBehavior
{
    function it_implements_product_variant_price_provider_interface(): void
    {
        $this->shouldImplement(ProductVariantLowestPriceCalculatorInterface::class);
    }

    function it_returns_the_lowest_price_before_discount(
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn($channelPricing);
        $channelPricing->getLowestPriceBeforeDiscount()->willReturn(2100);
        $channel->isLowestPriceForDiscountedProductsVisible()->willReturn(true);

        $this->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])->shouldReturn(2100);
    }

    function it_returns_null_when_showing_lowest_price_before_discount_is_turned_off_on_channel(
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn($channelPricing);
        $channel->isLowestPriceForDiscountedProductsVisible()->willReturn(false);

        $this->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])->shouldReturn(null);
    }

    function it_throws_a_channel_not_defined_exception_if_there_is_no_channel_pricing_when_providing_the_lowest_price_before_discount(
        ChannelInterface $channel,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn(null);
        $productVariant->getDescriptor()->willReturn('Red variant (RED_VARIANT)');
        $channel->getName()->shouldBeCalled();
        $channel->isLowestPriceForDiscountedProductsVisible()->shouldNotBeCalled();

        $this
            ->shouldThrow(MissingChannelConfigurationException::class)
            ->during('calculateLowestPriceBeforeDiscount', [$productVariant, ['channel' => $channel]])
        ;
    }
}
