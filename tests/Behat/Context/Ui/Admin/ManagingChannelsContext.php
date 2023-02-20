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
use Sylius\Behat\Page\Admin\Channel\UpdatePageInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Channel\DiscountedProductsCheckingPeriodInputElementInterface;
use Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Channel\LowestPriceFlagElementInterface;
use Webmozart\Assert\Assert;

final class ManagingChannelsContext implements Context
{
    public function __construct(
        private BaseManagingChannelsContext $managingChannelsContext,
        private LowestPriceFlagElementInterface $lowestPriceFlagElement,
        private DiscountedProductsCheckingPeriodInputElementInterface $discountedProductsCheckingPeriodInputElement,
        private UpdatePageInterface $updatePage,
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
     * @When /^I specify (-?\d+) days as the lowest price for discounted products checking period$/
     */
    public function iSpecifyDaysAsTheLowestPriceForDiscountedProductsCheckingPeriod(int $days): void
    {
        $this->discountedProductsCheckingPeriodInputElement->specifyPeriod($days);
    }

    /**
     * @Then I should be notified that the lowest price for discounted products checking period must be greater than 0
     */
    public function iShouldBeNotifiedThatTheLowestPriceForDiscountedProductsCheckingPeriodMustBeGreaterThanZero(): void
    {
        Assert::same(
            'Value must be greater than 0',
            $this->updatePage->getValidationMessage('discounted_products_checking_period'),
        );
    }

    /**
     * @Then I should be notified that the lowest price for discounted products checking period must be lower
     */
    public function iShouldBeNotifiedThatTheLowestPriceForDiscountedProductsCheckingPeriodMustBeLower(): void
    {
        Assert::same(
            'Value must be less than 2147483647',
            $this->updatePage->getValidationMessage('discounted_products_checking_period'),
        );
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

    /**
     * @Then /^the "[^"]+" channel should have the lowest price for discounted products checking period set to (\d+) days$/
     * @Then its lowest price for discounted products checking period should be set to :days days
     */
    public function theChannelShouldHaveTheLowestPriceForDiscountedProductsCheckingPeriodSetToDays(int $days): void
    {
        $lowestPriceForDiscountedProductsCheckingPeriod = $this->discountedProductsCheckingPeriodInputElement->getPeriod();

        Assert::same($days, $lowestPriceForDiscountedProductsCheckingPeriod);
    }
}
