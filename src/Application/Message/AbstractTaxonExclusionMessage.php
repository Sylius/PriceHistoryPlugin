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

namespace Sylius\PriceHistoryPlugin\Application\Message;

use Sylius\Bundle\ApiBundle\Command\ChannelCodeAwareInterface;
use Sylius\Bundle\ApiBundle\Command\IriToIdentifierConversionAwareInterface;

abstract class AbstractTaxonExclusionMessage implements ChannelCodeAwareInterface, IriToIdentifierConversionAwareInterface
{
    public ?string $channelCode = null;

    public ?string $taxonCode = null;

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }

    public function setChannelCode(?string $channelCode): void
    {
        $this->channelCode = $channelCode;
    }
}
