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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ApiClientInterface;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius\PriceHistoryPlugin\Model\ChannelInterface;
use Sylius\Behat\Context\Api\Admin\ManagingChannelsContext as BaseManagingChannelsContext;
use Webmozart\Assert\Assert;

final class ManagingChannelsContext implements Context
{
    public function __construct(
        private ApiClientInterface $client,
        private ResponseCheckerInterface $responseChecker,
        private BaseManagingChannelsContext $managingChannelsContext
    ) {
    }

    /**
     * @When I want to modify a channel :channel
     */
    public function iWantToModifyChannel(ChannelInterface $channel): void
    {
        $this->managingChannelsContext->iWantToModifyThisChannel($channel);
    }

    /**
     * @When /^I (enable|disable) showing the lowest price of discounted products$/
     */
    public function iEnableShowingTheLowestPriceOfDiscountedProducts(string $visible): void
    {
        $this->client->addRequestData('lowestPriceForDiscountedProductsVisible', $visible === 'enable');
    }

    /**
     * @Then /^it should have the lowest price of discounted products prior to the current discount (enabled|disabled)$/
     */
    public function itShouldHaveTheLowestPriceOfDiscountedProductsPriorToTheCurrentDiscountEnabledOrDisabled(
        string $visible
    ): void {
        $lowestPriceForDiscountedProductsVisible = $this->responseChecker->getValue(
            $this->client->getLastResponse(),
            'lowestPriceForDiscountedProductsVisible'
        );

        Assert::same($lowestPriceForDiscountedProductsVisible, $visible === 'enabled');
    }
}
