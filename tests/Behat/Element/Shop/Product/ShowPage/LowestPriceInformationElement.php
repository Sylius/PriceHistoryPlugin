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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Element\Shop\Product\ShowPage;

use FriendsOfBehat\PageObjectExtension\Element\Element;

final class LowestPriceInformationElement extends Element implements LowestPriceInformationElementInterface
{
    public function isThereInformationAboutProductLowestPriceWithPrice(string $lowestPriceBeforeDiscount): bool
    {
        return $this->hasElement('lowest_price_information_element_with_price', [
            '%lowestPriceBeforeDiscount%' => $lowestPriceBeforeDiscount,
        ]);
    }

    public function isThereInformationAboutProductLowestPrice(): bool
    {
        return $this->hasElement('lowest_price_information_element');
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'lowest_price_information_element' => '[data-test-product-lowest_price-before-the_discount]',
            'lowest_price_information_element_with_price' => '[data-test-product-lowest_price-before-the_discount="%lowestPriceBeforeDiscount%"]',
        ]);
    }
}
