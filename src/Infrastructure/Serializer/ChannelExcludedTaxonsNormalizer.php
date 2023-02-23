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

namespace Sylius\PriceHistoryPlugin\Infrastructure\Serializer;

use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class ChannelExcludedTaxonsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'sylius_price_history_channel_excluded_taxons_normalizer_already_called';

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, ChannelInterface::class);
        Assert::keyNotExists($context, self::ALREADY_CALLED);

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);
        Assert::isArray($data);

        return $data + [
            'taxonsExcludedFromShowingLowestPrice'=> $object->getTaxonsExcludedFromShowingLowestPrice()->getValues(),
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof ChannelInterface;
    }
}
