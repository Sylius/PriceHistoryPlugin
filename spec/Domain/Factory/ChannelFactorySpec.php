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

namespace spec\Sylius\PriceHistoryPlugin\Domain\Factory;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Channel\Factory\ChannelFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPriceHistoryConfigInterface;

final class ChannelFactorySpec extends ObjectBehavior
{
    function let(ChannelFactoryInterface $channelFactory, FactoryInterface $channelPriceHistoryConfigFactory): void
    {
        $this->beConstructedWith($channelFactory, $channelPriceHistoryConfigFactory);
    }

    function it_creates_a_new_channel_with_a_channel_price_history_config(
        ChannelFactoryInterface $channelFactory,
        FactoryInterface $channelPriceHistoryConfigFactory,
        ChannelInterface $channel,
        ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig,
    ): void {
        $channelFactory->createNew()->willReturn($channel);
        $channelPriceHistoryConfigFactory->createNew()->willReturn($channelPriceHistoryConfig);

        $channel->setChannelPriceHistoryConfig($channelPriceHistoryConfig)->shouldBeCalled();

        $this->createNew()->shouldReturn($channel);
    }

    function it_creates_named_channel_with_a_channel_price_history_config(
        ChannelFactoryInterface $channelFactory,
        FactoryInterface $channelPriceHistoryConfigFactory,
        ChannelInterface $channel,
        ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig,
    ): void {
        $channelFactory->createNamed('Fashion Store')->willReturn($channel);
        $channelPriceHistoryConfigFactory->createNew()->willReturn($channelPriceHistoryConfig);

        $channel->setChannelPriceHistoryConfig($channelPriceHistoryConfig)->shouldBeCalled();

        $this->createNamed('Fashion Store')->shouldReturn($channel);
    }
}
