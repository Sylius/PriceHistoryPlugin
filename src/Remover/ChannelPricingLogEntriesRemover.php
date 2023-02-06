<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\PriceHistoryPlugin\Remover;

use Doctrine\Persistence\ObjectManager;
use Sylius\PriceHistoryPlugin\Event\OldChannelPricingLogEntriesEvents;
use Sylius\PriceHistoryPlugin\Repository\ChannelPricingLogEntryRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class ChannelPricingLogEntriesRemover implements ChannelPricingLogEntriesRemoverInterface
{
    public function __construct(
        private ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntriesRepository,
        private ObjectManager $channelPricingLogEntriesManager,
        private EventDispatcherInterface $eventDispatcher,
        private int $batchSize = 100,
    ) {
    }

    public function remove(int $fromDays): void
    {
        $fromDate = new \DateTimeImmutable(sprintf('-%d days', $fromDays));
        while ([] !== $oldChannelPricingLogEntries = $this->getBatch($fromDate)) {
            foreach ($oldChannelPricingLogEntries as $oldChannelPricingLogEntry) {
                $this->channelPricingLogEntriesManager->remove($oldChannelPricingLogEntry);
            }

            $this->processDeletion($oldChannelPricingLogEntries);
        }
    }

    private function getBatch(\DateTimeInterface $fromDate): array
    {
        return $this->channelPricingLogEntriesRepository->findOlderThan($fromDate, $this->batchSize);
    }

    private function processDeletion(array $deletedChannelPricingLogEntries): void
    {
        $this->eventDispatcher->dispatch(new GenericEvent($deletedChannelPricingLogEntries), OldChannelPricingLogEntriesEvents::PRE_REMOVE);
        $this->channelPricingLogEntriesManager->flush();
        $this->eventDispatcher->dispatch(new GenericEvent($deletedChannelPricingLogEntries), OldChannelPricingLogEntriesEvents::POST_REMOVE);
        $this->channelPricingLogEntriesManager->clear();
    }
}
