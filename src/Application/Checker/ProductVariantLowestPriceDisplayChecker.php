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

namespace Sylius\PriceHistoryPlugin\Application\Checker;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Webmozart\Assert\Assert;

final class ProductVariantLowestPriceDisplayChecker implements ProductVariantLowestPriceDisplayCheckerInterface
{
    public function displayLowestPrice(ProductVariantInterface $productVariant, array $context): bool
    {
        Assert::keyExists($context, 'channel');
        $channel = $context['channel'];
        Assert::isInstanceOf($channel, ChannelInterface::class);

        if (!$channel->isLowestPriceForDiscountedProductsVisible()) {
            return false;
        }

        /** @var ProductInterface $product */
        $product = $productVariant->getProduct();

        $taxons = $product->getTaxons();
        if ($taxons->isEmpty()) {
            return true;
        }

        return 0 !== count(array_udiff(
            $product->getTaxons()->toArray(),
            $channel->getTaxonsExcludedFromShowingLowestPrice()->toArray(),
            /** @phpstan-ignore-next-line */
            fn (TaxonInterface $firstTaxon, TaxonInterface $secondTaxon): int => $firstTaxon->getCode() <=> $secondTaxon->getCode(),
        ));
    }
}
