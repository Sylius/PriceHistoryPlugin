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

class ChannelPricingLogEntry implements ChannelPricingLogEntryInterface
{
    private ?int $id = null;

    private \DateTimeInterface $loggedAt;

    public function __construct(
        private ChannelPricingInterface $channelPricing,
        private int $price,
        private ?int $originalPrice,
    ) {
        $this->loggedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannelPricing(): ChannelPricingInterface
    {
        return $this->channelPricing;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getOriginalPrice(): ?int
    {
        return $this->originalPrice;
    }

    public function getLoggedAt(): \DateTimeInterface
    {
        return $this->loggedAt;
    }
}
