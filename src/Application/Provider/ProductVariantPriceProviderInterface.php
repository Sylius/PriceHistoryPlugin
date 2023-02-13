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

namespace Sylius\PriceHistoryPlugin\Application\Provider;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;

interface ProductVariantPriceProviderInterface
{
    public function getLowestPriceBeforeDiscount(ProductVariantInterface $productVariant, ChannelInterface $channel): ?int;
}
