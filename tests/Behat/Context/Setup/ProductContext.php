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
use Sylius\Component\Core\Event\ProductUpdated;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ProductRepositoryInterface $productRepository,
        private ProductVariantResolverInterface $defaultVariantResolver,
        private MessageBusInterface $eventBus,
        private FactoryInterface $productVariantFactory,
        private FactoryInterface $channelPricingFactory,
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
     * @Given /^the (product "[^"]+") has a "([^"]+)" variant priced at ("[^"]+") and originally priced at ("[^"]+")$/
     */
    public function theProductHasVariantPricedAtAndOriginallyPricedAt(
        ProductInterface $product,
        string $productVariantName,
        int $price,
        int $originalPrice,
    ): void {
        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $this->channelPricingFactory->createNew();
        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);
        $channelPricing->setChannelCode($this->sharedStorage->get('channel')->getCode());

        /** @var ProductVariantInterface $variant */
        $variant = $this->productVariantFactory->createNew();
        $variant->setName($productVariantName);
        $variant->setCode(StringInflector::nameToUppercaseCode($productVariantName));
        $variant->setProduct($product);
        $variant->setOnHand(0);
        $variant->addChannelPricing($channelPricing);
        $variant->setShippingRequired(true);

        $product->setVariantSelectionMethod(ProductInterface::VARIANT_SELECTION_CHOICE);
        $product->addVariant($variant);

        $this->saveProduct($product);
    }

    /**
     * @Given /^(this product)'s price was discounted to ("[^"]+")$/
     * @Given /^(this product)'s discounted price changed to ("[^"]+")$/
     */
    public function thisProductsPriceWasDiscountedTo(ProductInterface $product, int $price): void
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->defaultVariantResolver->getVariant($product);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $productVariant->getChannelPricings()->first();

        Assert::notSame(
            $channelPricing->getOriginalPrice(),
            $price,
            'This is not a discount as the original price is the same as the current price.'
        );

        $channelPricing->setOriginalPrice($channelPricing->getPrice());
        $channelPricing->setPrice($price);

        $this->saveProduct($product);
    }

    /**
     * @Given /^(this product)'s discount was removed$/
     */
    public function thisProductsDiscountWasRemoved(ProductInterface $product): void
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->defaultVariantResolver->getVariant($product);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $productVariant->getChannelPricings()->first();

        $channelPricing->setPrice($channelPricing->getOriginalPrice());
        $channelPricing->setOriginalPrice(null);

        $this->saveProduct($product);
    }

    /**
     * @Given /^(this product)'s price was changed to ("[^"]+")$/
     * @Given /^(this product)'s price was changed back to ("[^"]+")$/
     */
    public function theProductChangedItsPriceTo(ProductInterface $product, int $price): void
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->defaultVariantResolver->getVariant($product);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $productVariant->getChannelPricingForChannel($this->sharedStorage->get('channel'));
        $channelPricing->setPrice($price);

        $this->saveProduct($product);
    }

    private function saveProduct(ProductInterface $product): void
    {
        $this->productRepository->add($product);
        $this->eventBus->dispatch(new ProductUpdated($product->getCode()));
        $this->sharedStorage->set('product', $product);
        $this->sharedStorage->set('variant', $product->getVariants()->first());
    }
}
