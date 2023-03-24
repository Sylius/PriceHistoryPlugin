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

use ApiPlatform\Core\Api\IriConverterInterface;
use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius1_11\Behat\Client\ApiClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class ManagingChannelPriceHistoryConfigsContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ApiClientInterface $channelClient,
        private ApiClientInterface $channelPriceHistoryConfigClient,
        private ResponseCheckerInterface $responseChecker,
        private IriConverterInterface $iriConverter,
    ) {
    }

    /**
     * @When /^I want to modify the price history config of (channel "[^"]+")$/
     */
    public function iWantToModifyThePriceHistoryConfigOfChannel(ChannelInterface $channel): void
    {
        $this->sharedStorage->set('channel', $channel);
        $this->channelPriceHistoryConfigClient->buildUpdateRequest(
            (string) $channel->getChannelPriceHistoryConfig()->getId(),
        );
    }

    /**
     * @When I save my changes
     * @When I try to save my changes
     */
    public function iSaveMyChanges(): void
    {
        $this->channelPriceHistoryConfigClient->update();
    }

    /**
     * @When /^I change showing of the lowest price of discounted products to be (enabled|disabled)$/
     */
    public function iChangeShowingOfTheLowestPriceOfDiscountedProducts(string $visible): void
    {
        $this->channelPriceHistoryConfigClient->addRequestData(
            'lowestPriceForDiscountedProductsVisible',
            $visible === 'enabled',
        );
    }

    /**
     * @When /^I change the lowest price for discounted products checking period to (-?\d+) days$/
     */
    public function iChangeTheLowestPriceForDiscountedProductsCheckingPeriodToDays(int $days): void
    {
        $this->channelPriceHistoryConfigClient->addRequestData('lowestPriceForDiscountedProductsCheckingPeriod', $days);
    }

    /**
     * @When I exclude the :taxon taxon from showing the lowest price of discounted products
     */
    public function iExcludeTheTaxonFromShowingTheLowestPriceOfDiscountedProducts(TaxonInterface $taxon): void
    {
        $this->iExcludeTheTaxonsFromShowingTheLowestPriceOfDiscountedProducts([$taxon]);
    }

    /**
     * @When /^I exclude the ("[^"]+" and "[^"]+" taxons) from showing the lowest price of discounted products$/
     */
    public function iExcludeTheTaxonsFromShowingTheLowestPriceOfDiscountedProducts(iterable $taxons): void
    {
        $taxonsIris = [];
        foreach ($taxons as $taxon) {
            $taxonsIris[] = $this->iriConverter->getIriFromItem($taxon);
        }

        $this->channelPriceHistoryConfigClient->addRequestData('taxonsExcludedFromShowingLowestPrice', $taxonsIris);
    }

    /**
     * @Then /^the ("[^"]+" channel) should have the lowest price of discounted products prior to the current discount (enabled|disabled)$/
     */
    public function theChannelShouldHaveTheLowestPriceOfDiscountedProductsPriorToTheCurrentDiscountEnabledOrDisabled(
        ChannelInterface $channel,
        string $visible,
    ): void {
        $lowestPriceForDiscountedProductsVisible = $this->responseChecker->getValue(
            $this->channelPriceHistoryConfigClient->show((string) $channel->getChannelPriceHistoryConfig()->getId()),
            'lowestPriceForDiscountedProductsVisible',
        );

        Assert::same($lowestPriceForDiscountedProductsVisible, $visible === 'enabled');
    }

    /**
     * @Then /^the ("[^"]+" channel) should have the lowest price for discounted products checking period set to (\d+) days$/
     * @Then /^(its) lowest price for discounted products checking period should be set to (\d+) days$/
     */
    public function theChannelShouldHaveTheLowestPriceForDiscountedProductsCheckingPeriodSetToDays(
        ChannelInterface $channel,
        int $days,
    ): void {
        $lowestPriceForDiscountedProductsCheckingPeriod = $this->responseChecker->getValue(
            $this->channelPriceHistoryConfigClient->show((string) $channel->getChannelPriceHistoryConfig()->getId()),
            'lowestPriceForDiscountedProductsCheckingPeriod',
        );

        Assert::same($lowestPriceForDiscountedProductsCheckingPeriod, $days);
    }

    /**
     * @Then I should be notified that it has been successfully edited
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyEdited(): void
    {
        Assert::true(
            $this->responseChecker->isUpdateSuccessful($this->channelPriceHistoryConfigClient->getLastResponse()),
            'Channel price history config could not be edited',
        );
    }

    /**
     * @Then I should be notified that the lowest price for discounted products checking period must be greater than 0
     */
    public function iShouldBeNotifiedThatTheLowestPriceForDiscountedProductsCheckingPeriodMustBeGreaterThanZero(): void
    {
        Assert::true($this->responseChecker->hasViolationWithMessage(
            $this->resolveLastResponse(),
            'Value must be greater than 0',
            'lowestPriceForDiscountedProductsCheckingPeriod',
        ));
    }

    /**
     * @Then I should be notified that the lowest price for discounted products checking period must be lower
     */
    public function iShouldBeNotifiedThatTheLowestPriceForDiscountedProductsCheckingPeriodMustBeLower(): void
    {
        Assert::true($this->responseChecker->hasViolationWithMessage(
            $this->resolveLastResponse(),
            'Value must be less than 2147483647',
            'lowestPriceForDiscountedProductsCheckingPeriod',
        ));
    }

    /**
     * @Then /^(this channel) should have ("[^"]+" taxon) excluded from displaying the lowest price of discounted products$/
     */
    public function thisChannelShouldHaveTaxonExcludedFromDisplayingTheLowestPriceOfDiscountedProducts(
        ChannelInterface $channel,
        TaxonInterface $taxon,
    ): void {
        $excludedTaxons = $this->responseChecker->getValue(
            $this->channelPriceHistoryConfigClient->show((string) $channel->getChannelPriceHistoryConfig()->getId()),
            'taxonsExcludedFromShowingLowestPrice',
        );

        Assert::true($this->isResourceAdminIriInArray($taxon, $excludedTaxons));
    }

    /**
     * @Then /^(this channel) should have ("([^"]+)" and "([^"]+)" taxons) excluded from displaying the lowest price of discounted products$/
     */
    public function thisChannelShouldHaveTaxonsExcludedFromDisplayingTheLowestPriceOfDiscountedProducts(
        ChannelInterface $channel,
        iterable $taxons,
    ): void {
        $excludedTaxons = $this->responseChecker->getValue(
            $this->channelPriceHistoryConfigClient->show((string) $channel->getChannelPriceHistoryConfig()->getId()),
            'taxonsExcludedFromShowingLowestPrice',
        );

        foreach ($taxons as $taxon) {
            Assert::true($this->isResourceAdminIriInArray($taxon, $excludedTaxons));
        }
    }

    /**
     * @Then /^(this channel) should not have ("[^"]+" taxon) excluded from displaying the lowest price of discounted products$/
     */
    public function thisChannelShouldNotHaveTaxonExcludedFromDisplayingTheLowestPriceOfDiscountedProducts(
        ChannelInterface $channel,
        TaxonInterface $taxon,
    ): void {
        $excludedTaxons = (array) $this->responseChecker->getValue(
            $this->channelPriceHistoryConfigClient->show((string) $channel->getChannelPriceHistoryConfig()->getId()),
            'taxonsExcludedFromShowingLowestPrice',
        );

        Assert::false($this->isResourceAdminIriInArray($taxon, $excludedTaxons));
    }

    private function resolveLastResponse(): Response
    {
        try {
            return $this->channelPriceHistoryConfigClient->getLastResponse();
        } catch (\Exception) {
            return $this->channelClient->getLastResponse();
        }
    }

    private function isResourceAdminIriInArray(ResourceInterface $resource, array $iris): bool
    {
        if (method_exists($this->iriConverter, 'getIriFromItemInSection')) {
            $iri = $this->iriConverter->getIriFromItemInSection($resource, 'admin');
        } else {
            $iri = $this->iriConverter->getIriFromItem($resource);
        }

        return in_array($iri, $iris, true);
    }
}
