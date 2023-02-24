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

namespace Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange;

use Sylius\PriceHistoryPlugin\Application\Logger\PriceChangeLoggerInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class CreateLogEntryOnPriceChange implements OnEntityChangeInterface
{
    public function __construct(private PriceChangeLoggerInterface $priceChangeLogger)
    {
    }

    /**
     * @param ChannelPricingInterface $entity
     */
    public function onChange(object $entity): void
    {
        $this->priceChangeLogger->log($entity);
    }

    public function getSupportedEntity(): string
    {
        return ChannelPricingInterface::class;
    }

    public function getSupportedFields(): array
    {
        return ['price', 'originalPrice'];
    }
}
