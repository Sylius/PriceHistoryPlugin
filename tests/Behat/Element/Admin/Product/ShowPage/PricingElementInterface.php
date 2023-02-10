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

interface PricingElementInterface
{
    public function getVariantPricingRowForChannel(string $variantName, string $channelName): NodeElement;

    public function getSimpleProductPricingRowForChannel($channelName): NodeElement;
}
