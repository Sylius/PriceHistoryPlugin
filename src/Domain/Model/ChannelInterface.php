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

use Sylius\Component\Core\Model\ChannelInterface as BaseChannelInterface;

interface ChannelInterface extends BaseChannelInterface
{
    public function isLowestPriceForDiscountedProductsVisible(): bool;

    public function setLowestPriceForDiscountedProductsVisible(bool $visible = true): void;
}
