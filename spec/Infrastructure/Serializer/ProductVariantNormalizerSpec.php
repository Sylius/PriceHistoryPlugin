<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\Serializer;

use phpDocumentor\Reflection\Types\Context;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\ApiBundle\SectionResolver\AdminApiSection;
use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Provider\ProductVariantPriceProviderInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProductVariantNormalizerSpec extends ObjectBehavior
{
    private const ALREADY_CALLED = 'sylius_price_history_product_variant_normalizer_already_called';

    function let(ProductVariantPriceProviderInterface $priceProvider, SectionProviderInterface $sectionProvider): void
    {
        $this->beConstructedWith($priceProvider, $sectionProvider);
    }

    function it_supports_only_product_variant_interface(
        ChannelInterface $channel,
        OrderInterface $order,
        ProductVariantInterface $variant,
    ): void {
        $this->supportsNormalization($variant, context: [ContextKeys::CHANNEL => $channel])->shouldReturn(true);
        $this->supportsNormalization($order, context: [ContextKeys::CHANNEL => $channel])->shouldReturn(false);
    }

    function it_supports_normalization_if_section_is_shop_get(
        SectionProviderInterface $sectionProvider,
        ChannelInterface $channel,
        ProductVariantInterface $variant,
        ShopApiSection $shopApiSection,
    ): void {
        $sectionProvider->getSection()->willReturn($shopApiSection);
        $this->supportsNormalization($variant, context: [ContextKeys::CHANNEL => $channel])->shouldReturn(true);
    }

    function it_does_not_support_if_section_is_admin_get(
        SectionProviderInterface $sectionProvider,
        AdminApiSection $adminApiSection,
        ChannelInterface $channel,
        ProductVariantInterface $variant,
    ): void {
        $sectionProvider->getSection()->willReturn($adminApiSection);
        $this->supportsNormalization($variant, context: [ContextKeys::CHANNEL => $channel])->shouldReturn(false);
    }

    function it_does_not_support_if_the_normalizer_has_been_already_called(ProductVariantInterface $variant): void
    {
        $this
            ->supportsNormalization($variant, null, [self::ALREADY_CALLED => true])
            ->shouldReturn(false)
        ;
    }

    function it_does_not_support_if_there_is_no_channel_in_the_context(ProductVariantInterface $variant): void
    {
        $this
            ->supportsNormalization($variant)
            ->shouldReturn(false)
        ;
    }

    function it_adds_lowest_price_before_discount_to_variant_data(
        ProductVariantPriceProviderInterface $priceProvider,
        NormalizerInterface $normalizer,
        ChannelInterface $channel,
        ProductVariantInterface $variant,
    ): void {
        $this->setNormalizer($normalizer);

        $channel->isLowestPriceForDiscountedProductsVisible()->willReturn(true);
        $context = [ContextKeys::CHANNEL => $channel];

        $normalizer
            ->normalize(
                $variant,
                null,
                array_merge($context, [self::ALREADY_CALLED => true])
            )
            ->willReturn([])
        ;

        $priceProvider->getLowestPriceBeforeDiscount($variant, $channel)->willReturn(3700);

        $this->normalize($variant, null, $context)->shouldBeLike(['lowestPriceBeforeDiscount' => 3700]);
    }

    function it_adds_null_lowest_price_before_discount_to_variant_data_when_channel_has_showing_turned_off(
        ProductVariantPriceProviderInterface $priceProvider,
        NormalizerInterface $normalizer,
        ChannelInterface $channel,
        ProductVariantInterface $variant,
    ): void {
        $this->setNormalizer($normalizer);

        $channel->isLowestPriceForDiscountedProductsVisible()->willReturn(false);
        $context = [ContextKeys::CHANNEL => $channel];

        $normalizer
            ->normalize(
                $variant,
                null,
                array_merge($context, [self::ALREADY_CALLED => true])
            )
            ->willReturn([])
        ;

        $priceProvider->getLowestPriceBeforeDiscount(Argument::cetera())->shouldNotBeCalled();

        $this->normalize($variant, null, $context)->shouldBeLike(['lowestPriceBeforeDiscount' => null]);
    }

    function it_does_not_add_lowest_price_before_discount_to_variant_data_if_missing_channel_configuration_exception_is_thrown(
        ProductVariantPriceProviderInterface $priceProvider,
        NormalizerInterface $normalizer,
        ChannelInterface $channel,
        ProductVariantInterface $variant,
    ): void {
        $this->setNormalizer($normalizer);

        $channel->isLowestPriceForDiscountedProductsVisible()->willReturn(true);
        $context = [ContextKeys::CHANNEL => $channel];

        $normalizer
            ->normalize(
                $variant,
                null,
                array_merge($context, [self::ALREADY_CALLED => true])
            )
            ->willReturn([])
        ;

        $priceProvider
            ->getLowestPriceBeforeDiscount($variant, $channel)
            ->willThrow(MissingChannelConfigurationException::class)
        ;

        $this->normalize($variant, null, $context)->shouldBeLike([]);
    }

    function it_throws_an_exception_if_the_normalizer_has_been_already_called(
        NormalizerInterface $normalizer,
        ProductVariantInterface $variant,
    ): void {
        $this->setNormalizer($normalizer);

        $normalizer->normalize($variant, null, [self::ALREADY_CALLED => true])->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('normalize', [$variant, null, [self::ALREADY_CALLED => true]])
        ;
    }

    function it_throws_an_exception_if_there_is_no_channel_in_the_context(
        NormalizerInterface $normalizer,
        ProductVariantInterface $variant,
    ): void {
        $this->setNormalizer($normalizer);

        $normalizer
            ->normalize($variant, null, [self::ALREADY_CALLED => true])
            ->willReturn([])
        ;

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'normalize',
                [$variant, null, [self::ALREADY_CALLED => false]]
            )
        ;
    }
}
