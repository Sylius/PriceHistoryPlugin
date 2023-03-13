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

namespace Sylius\PriceHistoryPlugin\Application\CommandDispatcher;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Command\ApplyLowestPriceOnChannelPricings;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class BatchedApplyLowestPriceOnChannelPricingsCommandDispatcher implements ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface
{
    public function __construct(
        private RepositoryInterface $channelPricingRepository,
        private MessageBusInterface $messageBus,
        private int $batchSize,
    ) {
    }

    public function applyWithinChannel(ChannelInterface $channel): void
    {
        $limit = $this->batchSize;
        $offset = 0;

        while ([] !== ($channelPricingsIds = $this->getIdsBatch($channel, $limit, $offset))) {
            $this->messageBus->dispatch(new ApplyLowestPriceOnChannelPricings($channelPricingsIds));

            $offset += $limit;
        }
    }

    private function getIdsBatch(ChannelInterface $channel, int $limit, int $offset): array
    {
        /** @var ChannelPricingInterface[] $channelPricings */
        $channelPricings = $this->channelPricingRepository->findBy(
            ['channelCode' => $channel->getCode()],
            ['id' => 'ASC'],
            $limit,
            $offset,
        );

        return (new ArrayCollection($channelPricings))
            ->map(fn (ChannelPricingInterface $channelPricing): mixed => $channelPricing->getId())
            ->getValues()
        ;
    }
}
