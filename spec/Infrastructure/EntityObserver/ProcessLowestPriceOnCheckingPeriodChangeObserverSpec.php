<?php

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\Doctrine\ORM\ChannelPricingLogEntryRepositoryInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver\EntityObserverInterface;

final class ProcessLowestPriceOnCheckingPeriodChangeObserverSpec extends ObjectBehavior
{
    function let(ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository): void
    {
        $this->beConstructedWith($channelPricingLogEntryRepository);
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
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelInterface $channel,
    ): void {
        $channelPricingLogEntryRepository->bulkUpdateLowestPricesBeforeDiscount($channel)->shouldBeCalled();

        $this->onChange($channel);
    }

    function it_throws_an_exception_if_entity_is_not_channel(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        OrderInterface $order,
    ): void {
        $channelPricingLogEntryRepository->bulkUpdateLowestPricesBeforeDiscount(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('onChange', [$order]);
    }
}
