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

namespace Sylius\PriceHistoryPlugin\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

trait ChannelPriceHistoryConfigAwareTrait
{
    /**
     * @ORM\OneToOne(targetEntity="Sylius\PriceHistoryPlugin\Domain\Model\ChannelPriceHistoryConfig", cascade={"all"})
     * @ORM\JoinColumn(name="channel_price_history_config_id", referencedColumnName="id", onDelete="CASCADE")
     */
    #[ORM\OneToOne(targetEntity: ChannelPriceHistoryConfig::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'channel_price_history_config_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig = null;

    public function getChannelPriceHistoryConfig(): ?ChannelPriceHistoryConfigInterface
    {
        return $this->channelPriceHistoryConfig;
    }

    public function setChannelPriceHistoryConfig(ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig): void
    {
        $this->channelPriceHistoryConfig = $channelPriceHistoryConfig;
    }
}
