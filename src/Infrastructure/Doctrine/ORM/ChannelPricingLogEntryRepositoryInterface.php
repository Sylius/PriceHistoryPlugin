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

namespace Sylius\PriceHistoryPlugin\Infrastructure\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

interface ChannelPricingLogEntryRepositoryInterface
{
    public function createByChannelPricingIdListQueryBuilder(mixed $channelPricingId): QueryBuilder;
}
