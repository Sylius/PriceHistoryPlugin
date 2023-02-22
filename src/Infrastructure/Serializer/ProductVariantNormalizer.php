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

use Sylius\Bundle\ApiBundle\SectionResolver\AdminApiSection;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Provider\ProductVariantPriceProviderInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class ProductVariantNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'sylius_price_history_product_variant_normalizer_already_called';

    public function __construct(
        private ProductVariantPriceProviderInterface $priceProvider,
        private SectionProviderInterface $uriBasedSectionContext,
    ) {
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, ProductVariantInterface::class);
        Assert::keyNotExists($context, self::ALREADY_CALLED);

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);
        Assert::isArray($data);

        /** @var ChannelInterface $channel */
        $channel = $context[ContextKeys::CHANNEL];
        if (false === $channel->isLowestPriceForDiscountedProductsVisible()) {
            return $data + ['lowestPriceBeforeDiscount' => null];
        }

        try {
            $data['lowestPriceBeforeDiscount'] = $this->priceProvider->getLowestPriceBeforeDiscount($object, $channel);
        } catch (MissingChannelConfigurationException) {
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED]) || !isset($context[ContextKeys::CHANNEL])) {
            return false;
        }

        return $data instanceof ProductVariantInterface && $this->isNotAdminApiSection();
    }

    private function isNotAdminApiSection(): bool
    {
        return !$this->uriBasedSectionContext->getSection() instanceof AdminApiSection;
    }
}
