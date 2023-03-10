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

namespace Tests\Sylius\PriceHistoryPlugin\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ChannelPricing as BaseChannelPricing;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceBeforeDiscountAwareTrait;

#[ORM\Table(name: 'sylius_channel_pricing')]
#[ORM\Entity]
class ChannelPricing extends BaseChannelPricing implements ChannelPricingInterface
{
    use LowestPriceBeforeDiscountAwareTrait;
}
