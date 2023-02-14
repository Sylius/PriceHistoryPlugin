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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Product\ShowPage;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Element\Element;

final class PricingElement extends Element implements PricingElementInterface
{
    public function getVariantPricingRowForChannel(string $variantName, string $channelName): NodeElement
    {
        return $this->getElement('variant_pricing_row', [
            '%variantName%' => $variantName,
            '%channelName%' => $channelName,
        ]);
    }

    public function getSimpleProductPricingRowForChannel(string $channelName): NodeElement
    {
        return $this->getElement('simple_product_pricing_row', ['%channelName%' => $channelName]);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'simple_product_pricing_row' => '#pricing tr:contains("%channelName%")',
            'variant_pricing_row' => 'tr:contains("%variantName%") + tr:contains("%channelName%")',
        ]);
    }
}
