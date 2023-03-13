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

namespace spec\Sylius\PriceHistoryPlugin\Application\CommandDispatcher;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Command\ApplyLowestPriceOnChannelPricings;
use Sylius\PriceHistoryPlugin\Application\CommandDispatcher\ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface;
use Sylius\PriceHistoryPlugin\Application\CommandHandler\ApplyLowestPriceOnChannelPricingsHandler;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class BatchedApplyLowestPriceOnChannelPricingsCommandDispatcherSpec extends ObjectBehavior
{
    function let(RepositoryInterface $channelPricingRepository, MessageBusInterface $messageBus): void
    {
        $this->beConstructedWith($channelPricingRepository, $messageBus, 2);
    }

    function it_implements_apply_lowest_price_on_channel_pricings_command_dispatcher_interface(): void
    {
        $this->shouldImplement(ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface::class);
    }

    function it_dispatches_applications_of_lowest_price_on_channel_pricing_within_channel_in_batches(
        RepositoryInterface $channelPricingRepository,
        MessageBusInterface $messageBus,
        ChannelInterface $channel,
        ChannelPricingInterface $firstChannelPricing,
        ChannelPricingInterface $secondChannelPricing,
        ChannelPricingInterface $thirdChannelPricing,
        ChannelPricingInterface $fourthChannelPricing,
        ChannelPricingInterface $fifthChannelPricing,
    ): void {
        $channel->getCode()->willReturn('WEB');

        $firstChannelPricing->getId()->willReturn(1);
        $secondChannelPricing->getId()->willReturn(2);
        $thirdChannelPricing->getId()->willReturn(6);
        $fourthChannelPricing->getId()->willReturn(7);
        $fifthChannelPricing->getId()->willReturn(9);

        $batches = [
            [
                $firstChannelPricing->getWrappedObject(),
                $secondChannelPricing->getWrappedObject(),
            ],
            [
                $thirdChannelPricing->getWrappedObject(),
                $fourthChannelPricing->getWrappedObject(),
            ],
            [
                $fifthChannelPricing->getWrappedObject(),
            ],
            [],
        ];

        $batchSize = 2;

        foreach ($batches as $key => $batch) {
            $channelPricingRepository
                ->findBy(['channelCode' => 'WEB'], ['id' => 'ASC'], 2, $key * $batchSize)
                ->willReturn($batch)
                ->shouldBeCalled()
            ;
        }

        foreach ([[1, 2], [6, 7], [9]] as $ids) {
            $messageBus
                ->dispatch($command = new ApplyLowestPriceOnChannelPricings($ids))
                ->willReturn(new Envelope($command))
                ->shouldBeCalled()
            ;
        }

        $this->applyWithinChannel($channel);
    }
}
