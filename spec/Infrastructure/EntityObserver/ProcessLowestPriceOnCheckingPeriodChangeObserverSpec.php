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

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\PriceHistoryPlugin\Application\CommandDispatcher\ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver\EntityObserverInterface;

final class ProcessLowestPriceOnCheckingPeriodChangeObserverSpec extends ObjectBehavior
{
    function let(ApplyLowestPriceOnChannelPricingsCommandDispatcherInterface $commandDispatcher): void
    {
        $this->beConstructedWith($commandDispatcher);
    }

    function it_implements_on_entity_observer_interface(): void
    {
        $this->shouldImplement(EntityObserverInterface::class);
    }

    function it_supports_channel_interface_only(
        ChannelInterface $channel,
        OrderInterface $order,
    ): void {
        $channel->getCode()->willReturn('test');

        $this->supports($channel)->shouldReturn(true);
        $this->supports($order)->shouldReturn(false);
    }

    function it_does_not_support_a_channel_that_is_currently_being_processed(
        ChannelInterface $channel,
    ): void {
        $channel->getCode()->willReturn('test');

        $object = $this->object->getWrappedObject();
        $objectReflection = new \ReflectionObject($object);
        $property = $objectReflection->getProperty('channelsCurrentlyProcessed');
        $property->setAccessible(true);
        $property->setValue($object, ['test' => true]);

        $this->supports($channel)->shouldReturn(false);
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
