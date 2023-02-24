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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Product\ShowPage\PricingElementInterface;
use Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Product\ShowPage\VariantsElementInterface;
use Webmozart\Assert\Assert;

final class ProductShowPageContext implements Context
{
    public function __construct(
        private PricingElementInterface $pricingElement,
        private VariantsElementInterface $variantsElement,
    ) {
    }

    /**
     * @When I access the price history of a simple product for :channelName channel
     */
    public function iAccessThePriceHistoryIndexPageOfSimpleProductForChannel(string $channelName): void
    {
        $pricingRow = $this->pricingElement->getSimpleProductPricingRowForChannel($channelName);
        $pricingRow->clickLink('Show');
    }

    /**
     * @When I access the price history of a product variant :variantName for :channelName channel
     */
    public function iAccessThePriceHistoryIndexPageOfVariantForChannel(string $variantName, string $channelName): void
    {
        $pricingRow = $this->pricingElement->getVariantPricingRowForChannel($variantName, $channelName);
        $pricingRow->clickLink('Show');
    }

    /**
     * @Then I should see :lowestPriceBeforeDiscount as its lowest price before the discount in :channelName channel
     */
    public function iShouldSeeAsItsLowestPriceBeforeTheDiscountInChannel(
        string $lowestPriceBeforeDiscount,
        string $channelName,
    ): void {
        Assert::same($this->pricingElement->getLowestPriceBeforeDiscountForChannel($channelName), $lowestPriceBeforeDiscount);
    }

    /**
     * @Then I should not see the lowest price before the discount in :channelName channel
     */
    public function iShouldNotSeeTheLowestPriceBeforeTheDiscountInChannel(string $channelName): void
    {
        Assert::same($this->pricingElement->getLowestPriceBeforeDiscountForChannel($channelName), '-');
    }

    /**
     * @Then I should see the lowest price before the discount of :lowestPriceBeforeDiscount for :variantName variant in :channelName channel
     */
    public function iShouldSeeVariantWithTheLowestPriceBeforeTheDiscountOfInChannel(
        string $lowestPriceBeforeDiscount,
        string $variantName,
        string $channelName,
    ): void {
        Assert::true($this->variantsElement->hasProductVariantWithLowestPriceBeforeDiscountInChannel(
            $variantName,
            $lowestPriceBeforeDiscount,
            $channelName,
        ));
    }

    /**
     * @Then I should not see the lowest price before the discount for :variantName variant in :channelName channel
     */
    public function iShouldNotSeeTheLowestPriceBeforeTheDiscountForVariantInChannel(
        string $variantName,
        string $channelName,
    ): void {
        Assert::true($this->variantsElement->hasProductVariantWithLowestPriceBeforeDiscountInChannel(
            $variantName,
            '-',
            $channelName,
        ));
    }
}
