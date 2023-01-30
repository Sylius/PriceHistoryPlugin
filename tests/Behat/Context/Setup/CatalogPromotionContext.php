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
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\CatalogPromotion\Calculator\FixedDiscountPriceCalculator;
use Sylius\Bundle\CoreBundle\CatalogPromotion\Calculator\PercentageDiscountPriceCalculator;
use Sylius\Bundle\CoreBundle\CatalogPromotion\Checker\InForProductScopeVariantChecker;
use Sylius\Bundle\CoreBundle\CatalogPromotion\Checker\InForVariantsScopeVariantChecker;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Promotion\Event\CatalogPromotionUpdated;
use Symfony\Component\Messenger\MessageBusInterface;

final class CatalogPromotionContext implements Context
{
    public function __construct(
        private ExampleFactoryInterface $catalogPromotionExampleFactory,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $eventBus,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @Given the :catalogPromotion catalog promotion is enabled
     */
    public function theCatalogPromotionIsEnabled(CatalogPromotionInterface $catalogPromotion): void
    {
        $catalogPromotion->setEnabled(true);
        $this->entityManager->flush();

        $this->eventBus->dispatch(new CatalogPromotionUpdated($catalogPromotion->getCode()));
    }

    /**
     * @Given there is disabled catalog promotion named :name
     */
    public function thereIsCatalogPromotionsNamed(string $name): void
    {
        $this->createCatalogPromotion(name: $name, enabled: false);

        $this->entityManager->flush();
    }

    /**
     * @Given /^there is a catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by ("[^"]+") and applies on ("[^"]+" product)$/
     */
    public function thereIsACatalogPromotionWithPriorityThatReducesPriceByAndAppliesOnProduct(
        string $name,
        int $priority,
        float $discount,
        ProductInterface $product,
    ): void {
        $catalogPromotion = $this->createCatalogPromotion(
            $name,
            [[
                'type' => InForProductScopeVariantChecker::TYPE,
                'configuration' => ['products' => [$product->getCode()]],
            ]],
            [[
                'type' => PercentageDiscountPriceCalculator::TYPE,
                'configuration' => ['amount' => $discount],
            ]],
            $priority
        );

        $this->entityManager->flush();

        $this->eventBus->dispatch(new CatalogPromotionUpdated($catalogPromotion->getCode()));
    }


    /**
     * @Given /^there is disabled catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" product)$/
     */
    public function thereIsACatalogPromotionWithPriorityThatReducesPriceByFixedInTheChannelAndAppliesOnProduct(
        string $name,
        int $priority,
        int $discount,
        ChannelInterface $channel,
        ProductInterface $product,
    ): void {
        $catalogPromotion = $this->createCatalogPromotion(
            $name,
            [[
                'type' => InForProductScopeVariantChecker::TYPE,
                'configuration' => ['products' => [$product->getCode()]],
            ]],
            [[
                'type' => FixedDiscountPriceCalculator::TYPE,
                'configuration' => [$channel->getCode() => ['amount' => $discount / 100]],
            ]],
            $priority,
            false,
        );

        $this->entityManager->flush();

        $this->eventBus->dispatch(new CatalogPromotionUpdated($catalogPromotion->getCode()));
    }

    private function createCatalogPromotion(
        string $name,
        array $scopes = [],
        array $actions = [],
        int $priority = null,
        bool $enabled = true,
    ): CatalogPromotionInterface {

        /** @var CatalogPromotionInterface $catalogPromotion */
        $catalogPromotion = $this->catalogPromotionExampleFactory->create([
            'name' => $name,
            'code' => StringInflector::nameToCode($name),
            'start_date' => null,
            'end_date' => null,
            'enabled' => $enabled,
            'channels' => $this->sharedStorage->has('channel') ? [$this->sharedStorage->get('channel')] : [],
            'actions' => $actions,
            'scopes' => $scopes,
            'description' => $name . ' description',
            'priority' => $priority,
            'exclusive' => false,
        ]);

        $this->entityManager->persist($catalogPromotion);
        $this->sharedStorage->set('catalog_promotion', $catalogPromotion);

        return $catalogPromotion;
    }
}
