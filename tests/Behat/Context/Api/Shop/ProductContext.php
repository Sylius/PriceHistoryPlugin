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

use ApiPlatform\Core\Api\IriConverterInterface;
use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ApiClientInterface;
use Sylius\Behat\Client\RequestFactoryInterface;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius\Behat\Service\Setter\ChannelContextSetterInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
{
    public function __construct(
        private ApiClientInterface $client,
        private ResponseCheckerInterface $responseChecker,
        private SharedStorageInterface $sharedStorage,
        private IriConverterInterface $iriConverter,
        private ChannelContextSetterInterface $channelContextSetter,
        private RequestFactoryInterface $requestFactory,
        private string $apiUrlPrefix,
    ) {
    }

    /**
     * @Then I should not see information about its lowest price
     */
    public function iShouldNotSeeInformationAboutItsLowestPrice(): void
    {
        $variant = $this->responseChecker->getResponseContent($this->client->getLastResponse());

        Assert::keyExists($variant, 'lowestPriceOfLastThirtyDays');
        Assert::same($variant['lowestPriceOfLastThirtyDays'], null);
    }

    /**
     * @Then /^I should see ("[^"]+") as this product's lowest price from 30 days before the discount$/
     */
    public function iShouldSeeAsThisProductsLowestPriceFromDaysBeforeTheDiscount(int $lowestPrice): void
    {
        $variant = $this->responseChecker->getResponseContent($this->client->getLastResponse());

        Assert::keyExists($variant, 'lowestPriceOfLastThirtyDays');
        Assert::same($variant['lowestPriceOfLastThirtyDays'], $lowestPrice);
    }
}
