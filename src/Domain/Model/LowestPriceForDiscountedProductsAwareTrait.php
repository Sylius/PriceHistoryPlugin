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

namespace Sylius\PriceHistoryPlugin\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

trait LowestPriceForDiscountedProductsAwareTrait
{
    /** @ORM\Column(name="lowest_price_for_discounted_products_checking_period", type="integer", nullable=false, options={"default": 30}) */
    #[ORM\Column(name: 'lowest_price_for_discounted_products_checking_period', type: 'integer', nullable: false, options: ['default' => 30])]
    protected int $lowestPriceForDiscountedProductsCheckingPeriod = 30;

    /** @ORM\Column(name="lowest_price_for_discounted_products_visible", type="boolean", nullable=false, options={"default": true}) */
    #[ORM\Column(name: 'lowest_price_for_discounted_products_visible', type: 'boolean', nullable: false, options: ['default' => true])]
    protected bool $lowestPriceForDiscountedProductsVisible = true;

    public function getLowestPriceForDiscountedProductsCheckingPeriod(): int
    {
        return $this->lowestPriceForDiscountedProductsCheckingPeriod;
    }

    public function setLowestPriceForDiscountedProductsCheckingPeriod(int $periodInDays): void
    {
        $this->lowestPriceForDiscountedProductsCheckingPeriod = $periodInDays;
    }

    public function isLowestPriceForDiscountedProductsVisible(): bool
    {
        return $this->lowestPriceForDiscountedProductsVisible;
    }

    public function setLowestPriceForDiscountedProductsVisible(bool $visible = true): void
    {
        $this->lowestPriceForDiscountedProductsVisible = $visible;
    }
}
