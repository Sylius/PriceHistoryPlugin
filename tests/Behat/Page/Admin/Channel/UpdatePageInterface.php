<?php

declare(strict_types=1);

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Channel\UpdatePageInterface as BaseUpdatePageInterface;

interface UpdatePageInterface extends BaseUpdatePageInterface
{
    public function enableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): void;

    public function disableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): void;

    public function isShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): bool;
}
