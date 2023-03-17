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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxonomy\Model\TaxonInterface;

trait LowestPriceForDiscountedProductsAwareTrait
{
    /** @ORM\Column(name="lowest_price_for_discounted_products_checking_period", type="integer", nullable=false, options={"default": 30}) */
    #[ORM\Column(name: 'lowest_price_for_discounted_products_checking_period', type: 'integer', nullable: false, options: ['default' => 30])]
    protected int $lowestPriceForDiscountedProductsCheckingPeriod = 30;

    /** @ORM\Column(name="lowest_price_for_discounted_products_visible", type="boolean", nullable=false, options={"default": true}) */
    #[ORM\Column(name: 'lowest_price_for_discounted_products_visible', type: 'boolean', nullable: false, options: ['default' => true])]
    protected bool $lowestPriceForDiscountedProductsVisible = true;

    /**
     * @var Collection|TaxonInterface[]
     *
     * @ORM\ManyToMany(targetEntity="Sylius\Component\Taxonomy\Model\TaxonInterface")
     * @ORM\JoinTable(name="sylius_channel_excluded_taxons",
     *     joinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="taxon_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    #[ORM\ManyToMany(targetEntity: TaxonInterface::class)]
    #[ORM\JoinTable(name: 'sylius_channel_excluded_taxons')]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'taxon_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Collection $taxonsExcludedFromShowingLowestPrice;

    public function __construct()
    {
        parent::__construct();

        $this->taxonsExcludedFromShowingLowestPrice = new ArrayCollection();
    }

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

    public function getTaxonsExcludedFromShowingLowestPrice(): Collection
    {
        return $this->taxonsExcludedFromShowingLowestPrice;
    }

    public function hasTaxonExcludedFromShowingLowestPrice(TaxonInterface $taxon): bool
    {
        return $this->taxonsExcludedFromShowingLowestPrice->contains($taxon);
    }

    public function addTaxonExcludedFromShowingLowestPrice(TaxonInterface $taxon): void
    {
        if (!$this->hasTaxonExcludedFromShowingLowestPrice($taxon)) {
            $this->taxonsExcludedFromShowingLowestPrice->add($taxon);
        }
    }

    public function removeTaxonExcludedFromShowingLowestPrice(TaxonInterface $taxon): void
    {
        if ($this->hasTaxonExcludedFromShowingLowestPrice($taxon)) {
            $this->taxonsExcludedFromShowingLowestPrice->removeElement($taxon);
        }
    }

    public function clearTaxonsExcludedFromShowingLowestPrice(): void
    {
        $this->taxonsExcludedFromShowingLowestPrice->clear();
    }
}
