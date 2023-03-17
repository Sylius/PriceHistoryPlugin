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
use Sylius\PriceHistoryPlugin\Application\Checker\ProductVariantLowestPriceDisplayCheckerInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ProductVariantLowestPriceCalculatorSpec extends ObjectBehavior
{
    function let(ProductVariantLowestPriceDisplayCheckerInterface $productVariantLowestPriceDisplayChecker): void
    {
        $this->beConstructedWith($productVariantLowestPriceDisplayChecker);
    }

    function it_implements_product_variant_price_provider_interface(): void
    {
        $this->shouldImplement(ProductVariantLowestPriceCalculatorInterface::class);
    }

    function it_returns_lowest_price_before_discount(
        ProductVariantLowestPriceDisplayCheckerInterface $productVariantLowestPriceDisplayChecker,
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn($channelPricing);
        $productVariantLowestPriceDisplayChecker->isLowestPriceDisplayable($productVariant, ['channel' => $channel])->willReturn(true);

        $channelPricing->getLowestPriceBeforeDiscount()->willReturn(2100);

        $this->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])->shouldReturn(2100);
    }

    function it_returns_null_when_showing_lowest_price_before_discount_should_not_be_displayed(
        ProductVariantLowestPriceDisplayCheckerInterface $productVariantLowestPriceDisplayChecker,
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn($channelPricing);
        $productVariantLowestPriceDisplayChecker->isLowestPriceDisplayable($productVariant, ['channel' => $channel])->willReturn(false);

        $channelPricing->getLowestPriceBeforeDiscount()->shouldNotBeCalled();

        $this->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])->shouldReturn(null);
    }

    function it_throws_a_channel_not_defined_exception_if_there_is_no_channel_pricing_when_providing_lowest_price_before_discount(
        ProductVariantLowestPriceDisplayCheckerInterface $productVariantLowestPriceDisplayChecker,
        ChannelInterface $channel,
        ProductVariantInterface $productVariant,
    ): void {
        $productVariant->getChannelPricingForChannel($channel)->willReturn(null);
        $productVariant->getDescriptor()->willReturn('Red variant (RED_VARIANT)');
        $channel->getName()->shouldBeCalled();
        $productVariantLowestPriceDisplayChecker
            ->isLowestPriceDisplayable($productVariant, ['channel' => $channel])
            ->shouldNotBeCalled()
        ;

        $this
            ->shouldThrow(MissingChannelConfigurationException::class)
            ->during('calculateLowestPriceBeforeDiscount', [$productVariant, ['channel' => $channel]])
        ;
    }

    function it_throws_an_exception_if_there_is_no_channel_passed_in_context(
        ProductVariantInterface $productVariant,
    ): void {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('calculateLowestPriceBeforeDiscount', [$productVariant, []])
        ;
    }

    function it_throws_an_exception_if_there_is_no_channel_set_under_the_channel_key_in_context(
        ProductVariantInterface $productVariant,
    ): void {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('calculateLowestPriceBeforeDiscount', [$productVariant, ['channel' => new \stdClass()]])
        ;
    }
}
