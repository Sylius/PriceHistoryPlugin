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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Tests\Sylius\PriceHistoryPlugin\Behat\Element\Shop\Product\ShowPage\LowestPriceInformationElementInterface;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
{
    public function __construct(
        private LowestPriceInformationElementInterface $informationAboutProductLowestPriceElement,
    ) {
    }

    /**
     * @Then I should not see information about its lowest price
     */
    public function iShouldNotSeeInformationAboutItsLowestPrice(): void
    {
        Assert::false($this->informationAboutProductLowestPriceElement->isThereInformationAboutProductLowestPrice());
    }

    /**
     * @Then /^I should see "([^"]+)" as its lowest price before the discount$/
     */
    public function iShouldSeeAsItsLowestPriceBeforeTheDiscount(string $lowestPriceBeforeDiscount): void
    {
        Assert::true($this->informationAboutProductLowestPriceElement->isThereInformationAboutProductLowestPriceWithPrice($lowestPriceBeforeDiscount));
    }
}
