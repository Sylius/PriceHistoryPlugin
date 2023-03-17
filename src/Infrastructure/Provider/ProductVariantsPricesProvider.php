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

namespace Sylius\PriceHistoryPlugin\Infrastructure\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface as BaseChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Provider\ProductVariantsPricesProviderInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\PriceHistoryPlugin\Application\Calculator\ProductVariantLowestPriceCalculatorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class ProductVariantsPricesProvider implements ProductVariantsPricesProviderInterface
{
    public function __construct(
        private ProductVariantLowestPriceCalculatorInterface $productVariantLowestPriceCalculator,
        private ProductVariantPricesCalculatorInterface $productVariantPriceCalculator,
        private MoneyFormatterInterface $moneyFormatter,
        private TranslatorInterface $translator,
    ) {
    }

    public function provideVariantsPrices(ProductInterface $product, BaseChannelInterface $channel): array
    {
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $variantsPrices = [];

        /** @var ProductVariantInterface $variant */
        foreach ($product->getEnabledVariants() as $variant) {
            $variantsPrices[] = $this->constructOptionsMap($variant, $channel);
        }

        return $variantsPrices;
    }

    private function constructOptionsMap(ProductVariantInterface $variant, ChannelInterface $channel): array
    {
        $optionMap = [];

        /** @var ProductOptionValueInterface $option */
        foreach ($variant->getOptionValues() as $option) {
            /** @var string $optionCode */
            $optionCode = $option->getOptionCode();
            $optionMap[$optionCode] = $option->getCode();
        }

        $price = $this->productVariantPriceCalculator->calculate($variant, ['channel' => $channel]);
        $optionMap['value'] = $price;

        /** @var CurrencyInterface $currency */
        $currency = $channel->getBaseCurrency();

        /** @var string $currencyCode */
        $currencyCode = $currency->getCode();

        $lowestPriceBeforeDiscount = $this->productVariantLowestPriceCalculator->calculateLowestPriceBeforeDiscount($variant, ['channel' => $channel]);

        if ($lowestPriceBeforeDiscount !== null) {
            $channelPriceHistoryConfig = $channel->getChannelPriceHistoryConfig();

            $optionMap['product-lowest-price-before-discount'] = $this->translator->trans(
                'sylius.ui.lowest_price_days_before_discount_was',
                [
                    '%days%' => $channelPriceHistoryConfig->getLowestPriceForDiscountedProductsCheckingPeriod(),
                    '%price%' => $this->moneyFormatter->format(
                        $lowestPriceBeforeDiscount,
                        $currencyCode,
                    ),
                ],
            );
        }

        if ($this->productVariantPriceCalculator instanceof ProductVariantPricesCalculatorInterface) {
            $originalPrice = $this->productVariantPriceCalculator->calculateOriginal($variant, ['channel' => $channel]);

            if ($originalPrice > $price) {
                $optionMap['original-price'] = $originalPrice;
            }
        }

        /** @var ArrayCollection $appliedPromotions */
        $appliedPromotions = $variant->getAppliedPromotionsForChannel($channel);
        if (!$appliedPromotions->isEmpty()) {
            $optionMap['applied_promotions'] = $appliedPromotions->toArray();
        }

        return $optionMap;
    }
}
