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
use Sylius\Behat\Page\Admin\ProductVariant\UpdatePageInterface;
use Sylius\Component\Core\Model\ChannelInterface;

final class ManagingProductVariantsContext implements Context
{
    public function __construct(private UpdatePageInterface $updatePage)
    {
    }

    /**
     * @When /^I change its price to "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/
     */
    public function iChangeItsPriceToForChannel(int $originalPrice, ChannelInterface $channel): void
    {
        $this->updatePage->specifyPrice($originalPrice, $channel);
    }
}
