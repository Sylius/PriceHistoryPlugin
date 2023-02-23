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

class ExcludeTaxonFromShowingLowestPrice extends AbstractTaxonExclusionMessage
{
    public static function createFromData(?string $channelCode, ?string $taxonCode): self
    {
        $command = new self();
        $command->channelCode = $channelCode;
        $command->taxonCode = $taxonCode;

        return $command;
    }
}
