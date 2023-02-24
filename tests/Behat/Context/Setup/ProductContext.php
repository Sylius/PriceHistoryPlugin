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
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
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
        $channelPricing = $this->getChannelPricingFromProduct($product);

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
     * @Given /^(this product)'s price changed to ("[^"]+")$/
     */
    public function thisProductsPriceChangedTo(ProductInterface $product, int $price): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);
        $channelPricing->setPrice($price);

        $this->saveProduct($product);
    }

    /**
     * @Given /^(this product)'s price changed to ("[^"]+") and original price changed to ("[^"]+")$/
     */
    public function thisProductsPriceChangedToAndOriginalPriceChangedTo(
        ProductInterface $product,
        int $price,
        int $originalPrice,
    ): void {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);

        $this->saveProduct($product);
    }

    /**
     * @Given /^(this variant)'s price changed to ("[^"]+") and original price changed to ("[^"]+")$/
     */
    public function thisVariantsPriceChangedToAndOriginalPriceChangedTo(
        ProductVariantInterface $productVariant,
        int $price,
        int $originalPrice,
    ): void {
        $channelPricing = $this->getChannelPricingFromVariant($productVariant);

        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);

        /** @var ProductInterface $product */
        $product = $productVariant->getProduct();

        $this->saveProduct($product);
    }

    /**
     * @Given /^(this product)'s price changed to ("[^"]+") and original price was removed$/
     */
    public function thisProductsPriceChangedToAndOriginalPriceWasRemoved(ProductInterface $product, $price): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);
        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice(null);

        $this->saveProduct($product);
    }

    /**
     * @Given /^on "([^"]+)" (its) price changed to ("[^"]+")$/
     */
    public function onDayItsPriceChangedTo(string $date, ProductInterface $product, int $price): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $this->calendarContext->itIsNow($date);
        $channelPricing->setPrice($price);

        $this->productVariantManager->flush();
    }

    /**
     * @Given /^on "([^"]+)" (its) original price changed to ("[^"]+")$/
     */
    public function onDayItsOriginalPriceChangedTo(string $date, ProductInterface $product, int $originalPrice): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $this->calendarContext->itIsNow($date);
        $channelPricing->setOriginalPrice($originalPrice);

        $this->productVariantManager->flush();
    }

    /**
     * @Given /^on "([^"]+)" (its) price changed to ("[^"]+") and original price to ("[^"]+")$/
     */
    public function onDayItsOriginalPriceChangedToAndOriginalPriceTo(string $date, ProductInterface $product, int $price, int $originalPrice): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $this->calendarContext->itIsNow($date);
        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);

        $this->productVariantManager->flush();
    }

    /**
     * @Given /^on "([^"]+)" (its) original price has been removed$/
     */
    public function onDayItsOriginalPriceHasBeenRemoved(string $date, ProductInterface $product): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $this->calendarContext->itIsNow($date);
        $channelPricing->setOriginalPrice(null);

        $this->productVariantManager->flush();
    }

    private function getChannelPricingFromProduct(ProductInterface $product): ChannelPricingInterface
    {
        $variant = $this->defaultVariantResolver->getVariant($product);
        Assert::notNull($variant);

        return $this->getChannelPricingFromVariant($variant);
    }

    private function getChannelPricingFromVariant(ProductVariantInterface $productVariant): ChannelPricingInterface
    {
        $channelPricing = $productVariant->getChannelPricings()->first();
        Assert::isInstanceOf($channelPricing, ChannelPricingInterface::class);

        return $channelPricing;
    }

    private function saveProduct(ProductInterface $product): void
    {
        $this->productRepository->add($product);
        $this->eventBus->dispatch(new ProductUpdated($product->getCode()));
        $this->sharedStorage->set('product', $product);
        $this->sharedStorage->set('variant', $product->getVariants()->first());
    }
}
