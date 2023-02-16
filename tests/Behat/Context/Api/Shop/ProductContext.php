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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Api\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius1_11\Behat\Client\ApiClientInterface;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
{
    public function __construct(
        private ApiClientInterface $client,
        private ResponseCheckerInterface $responseChecker,
    ) {
    }

    /**
     * @Then I should not see information about its lowest price
     */
    public function iShouldNotSeeInformationAboutItsLowestPrice(): void
    {
        $product = $this->responseChecker->getResponseContent($this->client->getLastResponse());
        $variant = $this->responseChecker->getResponseContent(
            $this->client->showByIri((string) $product['defaultVariant']),
        );

        Assert::keyExists($variant, 'lowestPriceBeforeDiscount');
        Assert::same($variant['lowestPriceBeforeDiscount'], null);
    }

    /**
     * @Then /^I should see ("[^"]+") as its lowest price before the discount$/
     */
    public function iShouldSeeAsItsLowestPriceBeforeTheDiscount(int $lowestPriceBeforeDiscount): void
    {
        $product = $this->responseChecker->getResponseContent($this->client->getLastResponse());
        $variant = $this->responseChecker->getResponseContent(
            $this->client->showByIri((string) $product['defaultVariant']),
        );

        Assert::keyExists($variant, 'lowestPriceBeforeDiscount');
        Assert::same($variant['lowestPriceBeforeDiscount'], $lowestPriceBeforeDiscount);
    }
}
