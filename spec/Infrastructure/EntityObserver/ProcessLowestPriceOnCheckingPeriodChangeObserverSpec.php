<?php

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver\EntityObserverInterface;

final class ProcessLowestPriceOnCheckingPeriodChangeObserverSpec extends ObjectBehavior
{
    function let(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
    ): void {
        $this->beConstructedWith($productLowestPriceBeforeDiscountProcessor, $channelPricingRepository);
    }

    function it_implements_on_entity_change_interface(): void
    {
        $this->shouldImplement(EntityObserverInterface::class);
    }

    function it_supports_channel_pricing_interface_only(
        ChannelInterface $channel,
        OrderInterface $order,
    ): void {
        $this->supports($channel)->shouldReturn(true);
        $this->supports($order)->shouldReturn(false);
    }

    function it_supports_lowest_price_for_discounted_products_checking_period_field(): void
    {
        $this->observedFields()->shouldReturn(['lowestPriceForDiscountedProductsCheckingPeriod']);
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

    function it_throws_an_exception_if_entity_is_not_channel(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
        OrderInterface $order,
    ): void {
        $channelPricingRepository->findBy(Argument::any())->shouldNotBeCalled();

        $productLowestPriceBeforeDiscountProcessor->process(Argument::any())->shouldNotBeCalled();
        $productLowestPriceBeforeDiscountProcessor->process(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('onChange', [$order]);
    }
}
