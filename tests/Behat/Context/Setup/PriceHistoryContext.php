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
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\PriceHistoryPlugin\Model\ChannelPricingLogEntryInterface;
use Sylius\PriceHistoryPlugin\Repository\ChannelPricingLogEntryRepositoryInterface;
use Webmozart\Assert\Assert;

final class PriceHistoryContext implements Context
{
    public function __construct(
        private ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        private ObjectManager $channelPricingManager,
        private ObjectManager $channelPricingLogEntryManager,
        private ProductVariantResolverInterface $defaultVariantResolver,
    ) {
    }

    /**
     * @Given /^(this product)'s price has been initially set to ("[^"]+") on "([^"]+)"$/
     */
    public function thisProductPriceHasBeenInitiallySetToOn(ProductInterface $product, int $price, string $date): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $this->clearChannelPricingLogs($channelPricing);

        $channelPricing->setPrice($price);

        $this->channelPricingManager->flush();

        $this->setLastLogEntryDate($channelPricing, $date);
    }

    /**
     * @Given /^on "([^"]+)" (its) price changed to ("[^"]+")$/
     */
    public function onDayItsPriceChangedTo(string $date, ProductInterface $product, int $price): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $channelPricing->setPrice($price);

        $this->channelPricingManager->flush();

        $this->setLastLogEntryDate($channelPricing, $date);
    }

    /**
     * @Given /^on "([^"]+)" (its) original price changed to ("[^"]+")$/
     */
    public function onDayItsOriginalPriceChangedTo(string $date, ProductInterface $product, int $originalPrice): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $channelPricing->setOriginalPrice($originalPrice);

        $this->channelPricingManager->flush();

        $this->setLastLogEntryDate($channelPricing, $date);
    }

    /**
     * @Given /^on "([^"]+)" (its) price changed to ("[^"]+") and original price to ("[^"]+")$/
     */
    public function onDayItsOriginalPriceChangedToAndOriginalPriceTo(string $date, ProductInterface $product, int $price, int $originalPrice): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice);

        $this->channelPricingManager->flush();

        $this->setLastLogEntryDate($channelPricing, $date);
    }

    /**
     * @Given /^on "([^"]+)" (its) original price has been removed$/
     */
    public function onDayItsOriginalPriceHasBeenRemoved(string $date, ProductInterface $product): void
    {
        $channelPricing = $this->getChannelPricingFromProduct($product);

        $channelPricing->setOriginalPrice(null);

        $this->channelPricingManager->flush();

        $this->setLastLogEntryDate($channelPricing, $date);
    }

    private function getChannelPricingFromProduct(ProductInterface $product): ChannelPricingInterface
    {
        $variant = $this->defaultVariantResolver->getVariant($product);
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        $channelPricing = $variant->getChannelPricings()->first();
        Assert::isInstanceOf($channelPricing, ChannelPricingInterface::class);

        return $channelPricing;
    }

    private function setLastLogEntryDate(ChannelPricingInterface $channelPricing, string $date): void
    {
        $logEntry = $this->getLastLogEntry($channelPricing);

        $property = new \ReflectionProperty($logEntry, 'loggedAt');
        $property->setAccessible(true);
        $property->setValue($logEntry, new \DateTimeImmutable($date));

        $this->channelPricingLogEntryManager->flush();
    }

    private function getLastLogEntry(ChannelPricingInterface $channelPricing): ChannelPricingLogEntryInterface
    {
        $logEntry = $this->channelPricingLogEntryRepository->findBy(
            ['channelPricing' => $channelPricing],
            ['id' => 'DESC'],
            1,
        )[0] ?? null;

        Assert::isInstanceOf($logEntry, ChannelPricingLogEntryInterface::class);

        return $logEntry;
    }

    private function clearChannelPricingLogs(ChannelPricingInterface $channelPricing): void
    {
        $logs = $this->channelPricingLogEntryRepository->findBy([
            'channelPricing' => $channelPricing,
        ]);
        foreach ($logs as $log) {
            $this->channelPricingLogEntryManager->remove($log);
        }
    }
}
