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

trait LowestPriceBeforeDiscountAwareTrait
{
    /** @ORM\Column(type="integer", nullable=true) */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $lowestPriceBeforeDiscount = null;

    public function getLowestPriceBeforeDiscount(): ?int
    {
        return $this->lowestPriceBeforeDiscount;
    }

    public function setLowestPriceBeforeDiscount(?int $lowestPriceBeforeDiscount): void
    {
        $this->lowestPriceBeforeDiscount = $lowestPriceBeforeDiscount;
    }
}
