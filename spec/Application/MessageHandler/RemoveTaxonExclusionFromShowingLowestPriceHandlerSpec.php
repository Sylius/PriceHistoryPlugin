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

namespace spec\Sylius\PriceHistoryPlugin\Application\MessageHandler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Message\RemoveTaxonExclusionFromShowingLowestPrice;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RemoveTaxonExclusionFromShowingLowestPriceHandlerSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository, TaxonRepositoryInterface $taxonRepository): void
    {
        $this->beConstructedWith($channelRepository, $taxonRepository);
    }

    function it_implements_message_handler_interface(): void
    {
        $this->shouldImplement(MessageHandlerInterface::class);
    }

    function it_throws_exception_when_channel_cannot_be_found(
        ChannelRepositoryInterface $channelRepository,
        TaxonRepositoryInterface $taxonRepository,
    ): void {
        $channelRepository->findOneByCode('WEB')->willReturn(null);

        $taxonRepository->findOneBy(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [
            RemoveTaxonExclusionFromShowingLowestPrice::createFromData('WEB', 'T_SHIRTS'),
        ]);
    }

    function it_throws_exception_when_taxon_cannot_be_found(
        ChannelRepositoryInterface $channelRepository,
        TaxonRepositoryInterface $taxonRepository,
        ChannelInterface $channel,
    ): void {
        $channelRepository->findOneByCode('WEB')->willReturn($channel);

        $taxonRepository->findOneBy(['code' => 'T_SHIRTS'])->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [
            RemoveTaxonExclusionFromShowingLowestPrice::createFromData('WEB', 'T_SHIRTS'),
        ]);
    }

    function it_excludes_a_taxon_from_showing_lowest_price_in_channel(
        ChannelRepositoryInterface $channelRepository,
        TaxonRepositoryInterface $taxonRepository,
        ChannelInterface $channel,
        TaxonInterface $taxon,
    ): void {
        $channelRepository->findOneByCode('WEB')->willReturn($channel);

        $taxonRepository->findOneBy(['code' => 'T_SHIRTS'])->willReturn($taxon);

        $channel->removeTaxonExcludedFromShowingLowestPrice($taxon)->shouldBeCalled();

        $this->__invoke(RemoveTaxonExclusionFromShowingLowestPrice::createFromData('WEB', 'T_SHIRTS'));
    }
}
