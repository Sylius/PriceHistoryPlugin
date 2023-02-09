<?php

declare(strict_types=1);

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Channel\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function enableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): void
    {
        $this->getDocument()->checkField('Show the lowest price of discounted products prior to the current discount?');
    }

    public function disableShowingTheLowestPriceOfDiscountedProductsPriorToTheDiscount(): void
    {
        $this->getDocument()->uncheckField('Show the lowest price of discounted products prior to the current discount?');
    }
}
