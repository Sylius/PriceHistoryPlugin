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

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EntityListener;

use PhpSpec\ObjectBehavior;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ChannelPricingChangeListenerSpec extends ObjectBehavior
{
    function let(ProductLowestPriceBeforeDiscountProcessorInterface $lowestPriceProcessor): void
    {
        $this->beConstructedWith($lowestPriceProcessor);
    }

    function it_processes_lowest_price_for_channel_pricing(
        ProductLowestPriceBeforeDiscountProcessorInterface $lowestPriceProcessor,
        ChannelPricingInterface $channelPricing,
    ): void {
        $lowestPriceProcessor->process($channelPricing)->shouldBeCalled();

        $this->postUpdate($channelPricing);
    }
}
