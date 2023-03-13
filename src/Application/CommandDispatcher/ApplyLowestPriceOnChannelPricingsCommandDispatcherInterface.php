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

namespace Sylius\PriceHistoryPlugin\Application\CommandDispatcher;

use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;

interface ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface
{
    public function applyWithinChannel(ChannelInterface $channel): void;
}
