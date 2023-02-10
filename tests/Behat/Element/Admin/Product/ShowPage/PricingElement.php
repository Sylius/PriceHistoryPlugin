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
use Webmozart\Assert\Assert;

final class PricingElement extends Element implements PricingElementInterface
{
    public function getVariantPricingRowForChannel(string $variantName, string $channelName): NodeElement
    {
        $pricingRow = $this->getDocument()->find(
            'css',
            sprintf('tr:contains("%s") + tr:contains("%s")', $variantName, $channelName),
        );

        Assert::notNull(
            $pricingRow,
            sprintf('Cannot find pricing row for variant "%s" in channel "%s"', $variantName, $channelName),
        );

        return $pricingRow;
    }

    public function getSimpleProductPricingRowForChannel($channelName): NodeElement
    {
        $pricingElement = $this->getElement('pricing_element');

        $pricingRow = $pricingElement->find(
            'css',
            sprintf('tr:contains("%s")', $channelName),
        );

        Assert::notNull(
            $pricingRow,
            sprintf('Cannot find pricing row for product in channel "%s"', $channelName),
        );

        return $pricingRow;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'pricing_element' => '#pricing',
        ]);
    }
}
