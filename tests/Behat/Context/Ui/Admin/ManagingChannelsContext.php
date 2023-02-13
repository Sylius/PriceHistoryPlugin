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
use Sylius\Behat\Context\Ui\Admin\ManagingChannelsContext as BaseManagingChannelsContext;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Channel\LowestPriceFlagElementInterface;
use Webmozart\Assert\Assert;

final class ManagingChannelsContext implements Context
{
    public function __construct(
        private BaseManagingChannelsContext $managingChannelsContext,
        private LowestPriceFlagElementInterface $lowestPriceFlagElement,
    ) {
    }

    /**
     * @When /^I (enable|disable) showing the lowest price of discounted products$/
     */
    public function iEnableShowingTheLowestPriceOfDiscountedProducts(string $visible): void
    {
        $this->lowestPriceFlagElement->$visible();
    }

    /**
     * @Then /^the ("[^"]+" channel) should have the lowest price of discounted products prior to the current discount (enabled|disabled)$/
     */
    public function theChannelShouldHaveTheLowestPriceOfDiscountedProductsPriorToTheCurrentDiscountEnabledOrDisabled(
        ChannelInterface $channel,
        string $visible,
    ): void {
        $this->managingChannelsContext->iWantToModifyChannel($channel);

        Assert::same(
            'enabled' === $visible,
            $this->lowestPriceFlagElement->isEnabled(),
        );
    }
}
