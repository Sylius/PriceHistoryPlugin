<?php

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange\OnEntityChangeInterface;

final class ProcessLowestPriceOnCheckingPeriodChangeSpec extends ObjectBehavior
{
    function let(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
    ): void {
        $this->beConstructedWith($productLowestPriceBeforeDiscountProcessor, $channelPricingRepository);
    }

    function it_implements_on_entity_change_interface(): void
    {
        $this->shouldImplement(OnEntityChangeInterface::class);
    }

    function it_supports_channel_pricing_interface(): void
    {
        $this->getSupportedEntity()->shouldReturn(ChannelInterface::class);
    }

    function it_supports_lowest_price_for_discounted_products_checking_period_field(): void
    {
        $this->getSupportedFields()->shouldReturn(['lowestPriceForDiscountedProductsCheckingPeriod']);
    }

    function it_processes_product_lowest_price_for_each_channel_pricing_within_channel(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
        ChannelInterface $channel,
        ChannelPricingInterface $firstChannelPricing,
        ChannelPricingInterface $secondChannelPricing,
    ): void {
        $channel->getCode()->willReturn('WEB');

        $channelPricingRepository->findBy(['channelCode' => 'WEB'])->willReturn([
            $firstChannelPricing->getWrappedObject(),
            $secondChannelPricing->getWrappedObject(),
        ]);

        $productLowestPriceBeforeDiscountProcessor->process($firstChannelPricing)->shouldBeCalled();
        $productLowestPriceBeforeDiscountProcessor->process($secondChannelPricing)->shouldBeCalled();

        $this->onChange($channel);
    }
}
