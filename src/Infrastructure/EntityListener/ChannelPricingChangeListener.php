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

namespace Sylius\PriceHistoryPlugin\Infrastructure\EntityListener;

use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ChannelPricingChangeListener
{
    public function __construct(private ProductLowestPriceBeforeDiscountProcessorInterface $lowestPriceProcessor)
    {
    }

    public function postUpdate(ChannelPricingInterface $channelPricing): void
    {
        $this->lowestPriceProcessor->process($channelPricing);
    }
}
