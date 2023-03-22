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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Channel\UpdatePage as BaseUpdatePage;

class UpdatePage extends BaseUpdatePage
{
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'discounted_products_checking_period' => '#sylius_channel_channelPriceHistoryConfig_lowestPriceForDiscountedProductsCheckingPeriod',
        ]);
    }
}
