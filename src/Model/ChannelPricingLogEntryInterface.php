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

namespace Sylius\PriceHistoryPlugin\Model;

use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ChannelPricingLogEntryInterface extends ResourceInterface
{
    public function getChannelPricing(): ChannelPricingInterface;

    public function getPrice(): int;

    public function getOriginalPrice(): ?int;

    public function isVisible(): bool;

    public function getLoggedAt(): \DateTimeInterface;
}
