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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class ManagingPriceHistory implements Context
{
    public function __construct(
        private RepositoryInterface $channelPricingLogEntryRepository,
        private ProductVariantResolverInterface $variantResolver,
    ) {
    }

    /**
     * @When I delete price history older than :days days?
     */
    public function iDeletePriceHistoryOlderThanDays(int $days): void
    {
        // TODO: implement //
    }

    /**
     * @Then /^there should be (\d+) price history entries for (this product)$/
     */
    public function thereShouldBeCountPriceHistoryEntriesForThisProduct(int $count, ProductInterface $product): void
    {
        $variant = $this->variantResolver->getVariant($product);
        Assert::notNull($variant);

        $channelPricingLogEntries = $this->channelPricingLogEntryRepository->findBy([
            'channelPricing' => $variant->getChannelPricings()->first(),
        ]);

        Assert::count($channelPricingLogEntries, $count);
    }

    /**
     * @Then /^this product's price history should be empty$/
     */
    public function thisProductsPriceHistoryShouldBeEmpty(ProductInterface $product): void
    {
        $this->thereShouldBeCountPriceHistoryEntriesForThisProduct(0, $product);
    }
}
