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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;

final class ChannelContext implements Context
{
    public function __construct(private EntityManagerInterface $channelManager)
    {
    }

    /**
     * @Given /^the (channel "[^"]+") has showing the lowest price of discounted products (enabled|disabled)$/
     */
    public function theChannelIsDisabled(ChannelInterface $channel, string $visible)
    {
        $channel->setLowestPriceForDiscountedProductsVisible($visible === 'enabled');

        $this->channelManager->flush();
    }

    /**
     * @Given /^(this channel) has (\d+) day(?:|s) set as the lowest price for discounted products checking period$/
     */
    public function thisChannelHasDaysSetAsTheLowestPriceForDiscountedProductsCheckingPeriod(
        ChannelInterface $channel,
        int $days,
    ): void {
        $channel->setLowestPriceForDiscountedProductsCheckingPeriod($days);

        $this->channelManager->flush();
    }

    /**
     * @Given /^the lowest price of discounted products prior to the current discount is disabled on (this channel)$/
     */
    public function theLowestPriceOfDiscountedProductsPriorToTheCurrentDiscountIsDisabledOnThisChannel(ChannelInterface $channel)
    {
        $channel->setLowestPriceForDiscountedProductsVisible(false);
    }

    /**
     * @Given /^the (channel "[^"]+") has ("([^"]+)" and "([^"]+)" taxons) excluded from showing the lowest price of discounted products$/
     */
    public function theTaxonAndTaxonAreExcludedFromShowingTheLowestPriceOfDiscountedProductsOnThisChannel(
        ChannelInterface $channel,
        iterable $taxons,
    ): void {
        /** @var TaxonInterface $taxon */
        foreach ($taxons as $taxon) {
            $channel->addTaxonExcludedFromShowingLowestPrice($taxon);
        }

        $this->channelManager->flush();
    }

    /**
     * @Given the :taxon taxon is excluded from showing the lowest price of discounted products in the :channel channel
     */
    public function theTaxonIsExcludedFromShowingTheLowestPriceOfDiscountedProductsInTheChannel(
        TaxonInterface $taxon,
        ChannelInterface $channel,
    ): void {
        $channel->addTaxonExcludedFromShowingLowestPrice($taxon);

        $this->channelManager->flush();
    }
}
