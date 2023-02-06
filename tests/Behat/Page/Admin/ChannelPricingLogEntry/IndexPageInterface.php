<?php

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\ChannelPricingLogEntry;

use Sylius\Behat\Page\Admin\Crud\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function isLogEntryWithPriceAndOriginalPrice(string $price, string $originalPrice): bool;

    public function isLogEntryWithPriceAndOriginalPriceOnPosition(string $price, string $originalPrice, int $position): bool;
}
