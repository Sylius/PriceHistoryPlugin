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

namespace Sylius\PriceHistoryPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ChannelPricingLogEntryFixture extends AbstractResourceFixture
{
    public function getName(): string
    {
        return 'channel_pricing_log_entry';
    }

    /** @psalm-suppress PossiblyNullReference */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode->children()->scalarNode('channel_pricing')->cannotBeEmpty();
    }
}
