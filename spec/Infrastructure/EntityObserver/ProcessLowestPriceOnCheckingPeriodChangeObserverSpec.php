<?php

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\CommandDispatcher\ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver\EntityObserverInterface;

final class ProcessLowestPriceOnCheckingPeriodChangeObserverSpec extends ObjectBehavior
{
    function let(ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface $commandDispatcher): void
    {
        $this->beConstructedWith($commandDispatcher);
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

    function it_delegates_processing_lowest_prices_to_command_dispatcher(
        ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface $commandDispatcher,
        ChannelInterface $channel,
    ): void {
        $commandDispatcher->applyWithinChannel($channel)->shouldBeCalled();

        $this->onChange($channel);
    }

    function it_throws_an_exception_if_entity_is_not_channel(
        ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface $commandDispatcher,
        OrderInterface $order,
    ): void {
        $commandDispatcher->applyWithinChannel(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('onChange', [$order]);
    }
}
