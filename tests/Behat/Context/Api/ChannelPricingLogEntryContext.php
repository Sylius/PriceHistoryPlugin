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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Api;

use ApiPlatform\Core\Api\IriConverterInterface;
use Behat\Behat\Context\Context;
use Sylius1_11\Behat\Client\ApiClientInterface;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

final class ChannelPricingLogEntryContext implements Context
{
    public function __construct(
        private ApiClientInterface $client,
        private ResponseCheckerInterface $responseChecker,
        private IriConverterInterface $iriConverter,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @When /^I go to the ("[^"]+" variant) price history$/
     */
    public function iGoToTheVariantPriceHistory(ProductVariantInterface $productVariant): void
    {
        Assert::notNull($channel = $this->sharedStorage->get('channel'));

        $this->client->index();
        $this->client->addFilter('channel', $this->iriConverter->getIriFromItem($channel));
        $this->client->addFilter('productVariant', $this->iriConverter->getIriFromItem($productVariant));
        $this->client->filter();
    }

    /**
     * @When I go to the product price history
     */
    public function iGoToTheProductPriceHistory(): void
    {
        $this->iGoToTheVariantPriceHistory($this->sharedStorage->get('variant'));
    }

    /**
     * @Then /^I should see (\d+) log entries in the catalog price history for the ("[^"]+" variant)$/
     */
    public function iShouldSeeLogEntriesInTheCatalogPriceHistoryForTheVariant(int $count, ProductVariantInterface $productVariant): void
    {
        Assert::same($this->responseChecker->countCollectionItems($this->client->getLastResponse()), $count);
    }

    /**
     * @Then /^I should see a single log entry in the catalog price history for the ("[^"]+" variant)$/
     */
    public function iShouldSeeASingleLogEntryInTheCatalogPriceHistoryForTheVariant(ProductVariantInterface $productVariant): void
    {
        $this->iShouldSeeLogEntriesInTheCatalogPriceHistoryForTheVariant(1, $productVariant);
    }

    /**
     * @Then /^there should be a log entry on the (\d+)(?:|st|nd|rd|th) position with the ("[^"]+") selling price, (no|"[^"]+") original price and datetime of the price change$/
     */
    public function thereShouldBeALogEntryOnThePositionWithTheSellingPriceOriginalPriceAndDatetimeOfThePriceChange(
        int $position,
        int $price,
        int|string $originalPrice,
    ): void
    {
        if ('no' === $originalPrice) {
            $originalPrice = null;
        }

        $logEntry = $this->responseChecker->getCollection($this->client->getLastResponse())[$position - 1];

        Assert::same($logEntry['price'], $price);
        Assert::same($logEntry['originalPrice'], $originalPrice);
        Assert::keyExists($logEntry, 'loggedAt');
    }

    /**
     * @Then /^there should be a log entry with the ("[^"]+") selling price, (no|"[^"]+") original price and datetime of the price change$/
     */
    public function thereShouldBeALogEntryWithTheSellingPriceOriginalPriceAndDatetimeOfThePriceChange(
        int $price,
        int|string $originalPrice,
    ): void {
        $this->thereShouldBeALogEntryOnThePositionWithTheSellingPriceOriginalPriceAndDatetimeOfThePriceChange(
            1,
            $price,
            $originalPrice
        );
    }
}
