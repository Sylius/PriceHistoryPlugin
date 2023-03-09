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

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Webmozart\Assert\Assert;

final class ProductVariantLowestPriceDisplayChecker implements ProductVariantLowestPriceDisplayCheckerInterface
{
    public function isLowestPriceDisplayable(ProductVariantInterface $productVariant, array $context): bool
    {
        Assert::keyExists($context, 'channel');
        $channel = $context['channel'];
        Assert::isInstanceOf($channel, ChannelInterface::class);

        if (!$channel->isLowestPriceForDiscountedProductsVisible()) {
            return false;
        }

        /** @var ProductInterface $product */
        $product = $productVariant->getProduct();

        return !$this->areAllTaxonsOfProductExcluded($product, $channel);
    }

    private function areAllTaxonsOfProductExcluded(ProductInterface $product, ChannelInterface $channel): bool
    {
        $taxons = $product->getTaxons();
        if ($taxons->isEmpty()) {
            return false;
        }

        $excludedTaxonsWithChildren = $this->getExcludedTaxonsWithChildren($channel->getTaxonsExcludedFromShowingLowestPrice());

        return 0 === count(array_udiff(
            $taxons->toArray(),
            $excludedTaxonsWithChildren,
            fn (TaxonInterface $firstTaxon, TaxonInterface $secondTaxon): int => $firstTaxon->getCode() <=> $secondTaxon->getCode(),
        ));
    }

    private function getExcludedTaxonsWithChildren(Collection $excludedTaxons): array
    {
        $excludedTaxonsWithChildren = $excludedTaxons->toArray();

        /** @var TaxonInterface $excludedTaxon */
        foreach ($excludedTaxons as $excludedTaxon) {
            $children = $excludedTaxon->getChildren();
            $excludedTaxonsWithChildren = array_merge($excludedTaxonsWithChildren, $children->toArray());
            if ($children->count() > 0) {
                $excludedTaxonsWithChildren = array_merge($excludedTaxonsWithChildren, $this->getExcludedTaxonsWithChildren($children));
            }
        }

        return array_unique($excludedTaxonsWithChildren, \SORT_REGULAR);
    }
}
