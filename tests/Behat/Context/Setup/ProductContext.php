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
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;

final class ProductContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ProductRepositoryInterface $productRepository,
        private ProductVariantResolverInterface $defaultVariantResolver,
    ) {
    }

    /**
     * @Given /^the ("[^"]+" product) is now priced at ("[^"]+") and originally priced at ("[^"]+")$/
     */
    public function theProductIsPricedAtAndOriginallyPricedAt(
        ProductInterface $product,
        int $price,
        int $originalPrice,
    ): void {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->defaultVariantResolver->getVariant($product);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $productVariant->getChannelPricings()->first();
        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);

        $this->saveProduct($product);
    }

    /**
     * @Given /^the ("[^"]+" variant) is now priced at ("[^"]+") and originally priced at ("[^"]+")$/
     */
    public function theProductVariantIsPricedAtAndOriginallyPricedAt(
        ProductVariantInterface $productVariant,
        int $price,
        int $originalPrice,
    ): void {
        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $productVariant->getChannelPricings()->first();
        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);

        $this->saveProduct($productVariant->getProduct());
    }

    private function saveProduct(ProductInterface $product): void
    {
        $this->productRepository->add($product);
        $this->sharedStorage->set('product', $product);
    }
}
