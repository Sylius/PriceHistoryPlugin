<?php

declare(strict_types=1);

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Channel\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function enableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): void;

    public function disableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): void;
}
