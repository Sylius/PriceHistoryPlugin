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

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Provider\ProductVariantsPricesProviderInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\PriceHistoryPlugin\Application\Calculator\ProductVariantLowestPriceCalculatorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductVariantsPricesProviderSpec extends ObjectBehavior
{
    function let(
        ProductVariantLowestPriceCalculatorInterface $productVariantLowestPriceCalculator,
        ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        MoneyFormatterInterface $moneyFormatter,
        TranslatorInterface $translator
    ): void {
        $this->beConstructedWith(
            $productVariantLowestPriceCalculator,
            $productVariantPricesCalculator,
            $moneyFormatter,
            $translator
        );
    }

    function it_implements_product_variants_prices_provider_interface(): void
    {
        $this->shouldImplement(ProductVariantsPricesProviderInterface::class);
    }

    function it_provides_only_variants_prices_when_there_is_no_discount(
        ArrayCollection $appliedPromotions,
        ChannelInterface $channel,
        CurrencyInterface $currency,
        ProductInterface $product,
        ProductOptionValueInterface $optionValue,
        ProductVariantInterface $variant,
        ProductVariantLowestPriceCalculatorInterface $productVariantLowestPriceCalculator,
        ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        TranslatorInterface $translator
    ): void {
        $product->getEnabledVariants()->willReturn(new ArrayCollection([$variant->getWrappedObject()]));
        $variant->getOptionValues()->willReturn(new ArrayCollection([$optionValue->getWrappedObject()]));
        $optionValue->getOptionCode()->willReturn('color');
        $optionValue->getCode()->willReturn('red');

        $productVariantPricesCalculator->calculate($variant, ['channel' => $channel])->willReturn(20);
        $channel->getBaseCurrency()->willReturn($currency);
        $currency->getCode()->willReturn('USD');
        $productVariantLowestPriceCalculator->calculateLowestPriceBeforeDiscount($variant, ['channel' => $channel])->willReturn(null);
        $productVariantPricesCalculator->calculateOriginal($variant, ['channel' => $channel])->willReturn(20);

        $appliedPromotions->isEmpty()->willReturn(true);
        $variant->getAppliedPromotionsForChannel($channel)->willReturn($appliedPromotions);

        $channel->getLowestPriceForDiscountedProductsCheckingPeriod()->willReturn(30);
        $translator->trans(
            'sylius.ui.lowest_price_days_before_discount_was',
            [
                '%days%' => 30,
                '%price%' => '$20.00',
            ]
        )->shouldNotBeCalled();

        $this->provideVariantsPrices($product, $channel)->shouldBeLike([
            [
                'color' => 'red',
                'value' => 20,
            ],
        ]);
    }

    function it_provides_lowest_price_information_when_there_is_a_discount(
        ArrayCollection $appliedPromotions,
        ChannelInterface $channel,
        CurrencyInterface $currency,
        MoneyFormatterInterface $moneyFormatter,
        ProductInterface $product,
        ProductOptionValueInterface $optionValue,
        ProductVariantInterface $variant,
        ProductVariantLowestPriceCalculatorInterface $productVariantLowestPriceCalculator,
        ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        TranslatorInterface $translator
    ): void {
        $product->getEnabledVariants()->willReturn(new ArrayCollection([$variant->getWrappedObject()]));
        $variant->getOptionValues()->willReturn(new ArrayCollection([$optionValue->getWrappedObject()]));
        $optionValue->getOptionCode()->willReturn('color');
        $optionValue->getCode()->willReturn('red');

        $productVariantPricesCalculator->calculate($variant, ['channel' => $channel])->willReturn(10);
        $channel->getBaseCurrency()->willReturn($currency);
        $currency->getCode()->willReturn('USD');
        $productVariantPricesCalculator->calculateOriginal($variant, ['channel' => $channel])->willReturn(20);
        $productVariantLowestPriceCalculator->calculateLowestPriceBeforeDiscount($variant, ['channel' => $channel])->willReturn(20);

        $appliedPromotions->isEmpty()->willReturn(true);
        $variant->getAppliedPromotionsForChannel($channel)->willReturn($appliedPromotions);

        $channel->getLowestPriceForDiscountedProductsCheckingPeriod()->willReturn(30);
        $moneyFormatter->format(20, 'USD')->willReturn('$20.00');

        $translator->trans(
            'sylius.ui.lowest_price_days_before_discount_was',
            [
                '%days%' => 30,
                '%price%' => '$20.00',
            ]
        )->willReturn('The lowest price of this product from 30 days prior to the current discount was $20.00');

        $this->provideVariantsPrices($product, $channel)->shouldBeLike([
            [
                'color' => 'red',
                'value' => 10,
                'product-lowest-price-before-the-discount' => 'The lowest price of this product from 30 days prior to the current discount was $20.00',
                'original-price' => 20,
            ],
        ]);
    }
}
