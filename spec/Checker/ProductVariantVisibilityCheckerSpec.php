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

namespace spec\Sylius\PriceHistoryPlugin\Checker;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Checker\ProductVariantVisibilityCheckerInterface;

class ProductVariantVisibilityCheckerSpec extends ObjectBehavior
{
    function it_implements_product_visibility_checker_interface(): void
    {
        $this->shouldHaveType(ProductVariantVisibilityCheckerInterface::class);
    }

    function it_returns_true_if_product_variant_is_visible(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductInterface $product,
    ): void {
        $productVariant->getProduct()->willReturn($product);

        $productVariant->isEnabled()->willReturn(true);
        $product->isEnabled()->willReturn(true);
        $product->hasChannel($channel)->willReturn(true);

        $this->isVisibleInChannel($productVariant, $channel)->shouldReturn(true);
    }

    function it_returns_false_if_product_is_disabled(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductInterface $product,
    ): void {
        $productVariant->getProduct()->willReturn($product);

        $productVariant->isEnabled()->willReturn(true);
        $product->isEnabled()->willReturn(false);
        $product->hasChannel($channel)->willReturn(true);

        $this->isVisibleInChannel($productVariant, $channel)->shouldReturn(false);
    }

    function it_returns_false_if_product_is_disabled_for_a_given_channel(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductInterface $product,
    ): void {
        $productVariant->getProduct()->willReturn($product);

        $productVariant->isEnabled()->willReturn(true);
        $product->isEnabled()->willReturn(true);
        $product->hasChannel($channel)->willReturn(false);

        $this->isVisibleInChannel($productVariant, $channel)->shouldReturn(false);
    }

    function it_returns_false_if_product_variant_is_disabled(
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ProductInterface $product,
    ): void {
        $productVariant->getProduct()->willReturn($product);

        $productVariant->isEnabled()->willReturn(false);
        $product->isEnabled()->willReturn(true);
        $product->hasChannel($channel)->willReturn(true);

        $this->isVisibleInChannel($productVariant, $channel)->shouldReturn(false);
    }
}
