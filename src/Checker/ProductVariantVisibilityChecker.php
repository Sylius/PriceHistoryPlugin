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

namespace Sylius\PriceHistoryPlugin\Checker;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

class ProductVariantVisibilityChecker implements ProductVariantVisibilityCheckerInterface
{
    public function isVisibleInChannel(ProductVariantInterface $productVariant, ChannelInterface $channel): bool
    {
        $product = $productVariant->getProduct();
        Assert::notNull($product);

        return $productVariant->isEnabled() && $product->isEnabled() && $product->hasChannel($channel);
    }
}
