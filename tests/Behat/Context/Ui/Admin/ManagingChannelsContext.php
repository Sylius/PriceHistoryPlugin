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
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\Channel\CreatePageInterface;
use Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\Channel\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingChannelsContext implements Context
{
    public function __construct(
        private BaseManagingChannelsContext $managingChannelsContext,
        private CreatePageInterface $createPage,
        private UpdatePageInterface $updatePage,
        private CurrentPageResolverInterface $currentPageResolver,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @When /^I (enable|disable) showing the lowest price of discounted products$/
     */
    public function iEnableShowingTheLowestPriceOfDiscountedProducts(string $visible): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);

        if ('enable' === $visible) {
            $currentPage->enableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount();
        } else {
            $currentPage->disableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount();
        }
    }

    /**
     * @Then /^the ("[^"]+" channel) should have the lowest price of discounted products prior to the current discount (enabled|disabled)$/
     */
    public function itShouldHaveTheLowestPriceOfDiscountedProductsPriorToTheCurrentDiscountEnabledOrDisabled(
        ChannelInterface $channel,
        string $visible,
    ): void {
        $this->managingChannelsContext->iWantToModifyChannel($channel);

        if ('enabled' === $visible) {
            Assert::true($this->updatePage->isShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount());
        } else {
            Assert::false($this->updatePage->isShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount());
        }
    }
}
