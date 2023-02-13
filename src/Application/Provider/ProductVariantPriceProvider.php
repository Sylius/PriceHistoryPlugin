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

use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ProductVariantPriceProvider implements ProductVariantPriceProviderInterface
{
    public function getLowestPriceBeforeDiscount(ProductVariantInterface $productVariant, ChannelInterface $channel): ?int
    {
        /** @var ChannelPricingInterface|null $channelPricing */
        $channelPricing = $productVariant->getChannelPricingForChannel($channel);

        if (null === $channelPricing) {
            throw MissingChannelConfigurationException::createForProductVariantChannelPricing($productVariant, $channel);
        }

        return $channelPricing->getLowestPriceBeforeDiscount();
    }
}
