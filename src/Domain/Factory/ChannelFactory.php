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

namespace Sylius\PriceHistoryPlugin\Domain\Factory;

use Sylius\Component\Channel\Factory\ChannelFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPriceHistoryConfigInterface;

final class ChannelFactory implements ChannelFactoryInterface
{
    public function __construct(
        private ChannelFactoryInterface $decorated,
        private FactoryInterface $channelPriceHistoryConfigFactory,
    ) {
    }

    public function createNew(): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->decorated->createNew();
        /** @var ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig */
        $channelPriceHistoryConfig = $this->channelPriceHistoryConfigFactory->createNew();
        $channel->setChannelPriceHistoryConfig($channelPriceHistoryConfig);

        return $channel;
    }

    public function createNamed(string $name): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->decorated->createNamed($name);
        /** @var ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig */
        $channelPriceHistoryConfig = $this->channelPriceHistoryConfigFactory->createNew();
        $channel->setChannelPriceHistoryConfig($channelPriceHistoryConfig);

        return $channel;
    }
}
