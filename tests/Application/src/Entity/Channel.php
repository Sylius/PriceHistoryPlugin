<?php

declare(strict_types=1);

namespace Tests\Sylius\PriceHistoryPlugin\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceForDiscountedProductsCheckingPeriodTrait;
use Sylius\PriceHistoryPlugin\Domain\Model\LowestPriceForDiscountedProductsVisibleAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements ChannelInterface
{
    use LowestPriceForDiscountedProductsVisibleAwareTrait;
    use LowestPriceForDiscountedProductsCheckingPeriodTrait;
}
