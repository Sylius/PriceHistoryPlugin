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

namespace Sylius\PriceHistoryPlugin\Domain\Repository;

use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingLogEntryInterface;

interface ChannelPricingLogEntryRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array|ChannelPricingLogEntryInterface[]
     */
    public function findOlderThan(\DateTimeInterface $date, ?int $limit = null): array;

    public function bulkUpdateLowestPricesBeforeDiscount(ChannelInterface $channel): void;

    public function findLowestPricesBeforeDiscount(
        ChannelPricingInterface $channelPricing,
        int $lowestPriceForDiscountedProductsCheckingPeriod,
    ): ?int;
}
