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

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ChannelExcludedTaxonsNormalizerSpec extends ObjectBehavior
{
    private const ALREADY_CALLED = 'sylius_price_history_channel_excluded_taxons_normalizer_already_called';

    function it_does_not_support_normalization_when_the_normalizer_has_already_been_called(
        ChannelInterface $channel,
    ): void {
        $this->supportsNormalization($channel, context: [self::ALREADY_CALLED => true])->shouldReturn(false);
    }

    function it_does_not_support_normalization_when_object_is_not_a_channel(): void
    {
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_throws_an_exception_when_normalizing_an_object_that_is_not_a_channel(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('normalize', [new \stdClass()]);
    }

    function it_throws_an_exception_when_it_has_already_been_called(ChannelInterface $channel): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('normalize', [$channel, null, [self::ALREADY_CALLED => true]])
        ;
    }

    function it_throws_an_exception_when_inner_normalizer_does_not_return_an_array(
        NormalizerInterface $normalizer,
        ChannelInterface $channel,
    ): void {
        $this->setNormalizer($normalizer);

        $normalizer->normalize($channel, null,  [self::ALREADY_CALLED => true])->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('normalize', [$channel])
        ;
    }

    function it_normalizes_excluded_taxons_collection(
        NormalizerInterface $normalizer,
        ChannelInterface $channel,
        TaxonInterface $firstTaxon,
        TaxonInterface $secondTaxon,
    ): void {
        $this->setNormalizer($normalizer);

        $channel->getTaxonsExcludedFromShowingLowestPrice()->willReturn(new ArrayCollection([
            1 => $firstTaxon->getWrappedObject(),
            3 => $secondTaxon->getWrappedObject(),
        ]));

        $normalizer->normalize($channel, null,  [self::ALREADY_CALLED => true])->willReturn([]);

        $this->normalize($channel)->shouldReturn(['taxonsExcludedFromShowingLowestPrice' => [
            $firstTaxon,
            $secondTaxon,
        ]]);
    }
}
