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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;

final class ChannelContext implements Context
{
    public function __construct(private EntityManagerInterface $channelManager)
    {
    }

    /**
     * @Given /^the (channel "[^"]+") has showing the lowest price of discounted products (enabled|disabled)$/
     */
    public function theChannelIsDisabled(ChannelInterface $channel, string $visible)
    {
        $channel->setLowestPriceForDiscountedProductsVisible($visible === 'enabled');

        $this->channelManager->flush();
    }
}
