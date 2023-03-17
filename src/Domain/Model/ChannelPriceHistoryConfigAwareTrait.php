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
    /** @ORM\OneToOne(targetEntity=ChannelPriceHistoryConfig::class, cascade={"persist", "remove"}) */
    /** @ORM\JoinColumn(name="channel_price_history_config_id", referencedColumnName="id") */
    #[ORM\OneToOne(targetEntity: ChannelPriceHistoryConfig::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'channel_price_history_config_id', referencedColumnName: 'id')]
    protected ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig;

    public function __construct()
    {
        parent::__construct();
    }

    public function getChannelPriceHistoryConfig(): ChannelPriceHistoryConfig
    {
        return $this->channelPriceHistoryConfig;
    }
}
