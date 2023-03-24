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

namespace spec\Sylius\PriceHistoryPlugin\Application\Templating\Helper;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Calculator\ProductVariantLowestPriceCalculatorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Templating\Helper\Helper;

final class PriceHelperSpec extends ObjectBehavior
{
    function let(ProductVariantLowestPriceCalculatorInterface $productVariantPriceCalculator): void
    {
        $this->beConstructedWith($productVariantPriceCalculator);
    }

    function it_is_a_templating_helper(): void
    {
        $this->shouldHaveType(Helper::class);
    }

    function it_throws_an_exception_if_channel_is_not_provided_when_getting_lowest_price(ProductVariantInterface $productVariant): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('getLowestPriceBeforeDiscount', [$productVariant, []])
        ;
    }

    function it_returns_lowest_price_before_discount(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductVariantLowestPriceCalculatorInterface $productVariantPriceCalculator,
    ): void {
        $productVariantPriceCalculator
            ->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->willReturn(1000)
        ;

        $this
            ->getLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->shouldReturn(1000)
        ;
    }

    function it_returns_null_when_lowest_price_before_discount_is_unavailable(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductVariantLowestPriceCalculatorInterface $productVariantPriceCalculator,
    ): void {
        $productVariantPriceCalculator
            ->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->willReturn(null)
        ;

        $this
            ->getLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->shouldReturn(null)
        ;
    }

    function it_throws_an_exception_if_channel_is_not_provided_when_checking_if_lowest_price_is_available(ProductVariantInterface $productVariant): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('hasLowestPriceBeforeDiscount', [$productVariant, []])
        ;
    }

    function it_returns_true_if_lowest_price_before_discount_is_available(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductVariantLowestPriceCalculatorInterface $productVariantPriceCalculator,
    ): void {
        $productVariantPriceCalculator
            ->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->willReturn(1000)
        ;

        $this
            ->hasLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->shouldReturn(true)
        ;
    }

    function it_returns_false_if_lowest_price_before_discount_is_unavailable(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductVariantLowestPriceCalculatorInterface $productVariantPriceCalculator,
    ): void {
        $productVariantPriceCalculator
            ->calculateLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->willReturn(null)
        ;

        $this
            ->hasLowestPriceBeforeDiscount($productVariant, ['channel' => $channel])
            ->shouldReturn(false)
        ;
    }

    function it_has_a_name(): void
    {
        $this->getName()->shouldReturn('sylius_price_history_calculate_price');
    }
}
