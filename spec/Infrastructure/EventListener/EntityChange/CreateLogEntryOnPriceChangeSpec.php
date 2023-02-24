<?php

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange;

use PhpSpec\ObjectBehavior;
use Sylius\PriceHistoryPlugin\Application\Logger\PriceChangeLoggerInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\EventListener\EntityChange\OnEntityChangeInterface;

final class CreateLogEntryOnPriceChangeSpec extends ObjectBehavior
{
    function let(PriceChangeLoggerInterface $priceChangeLogger): void
    {
        $this->beConstructedWith($priceChangeLogger);
    }

    function it_implements_on_entity_change_interface(): void
    {
        $this->shouldImplement(OnEntityChangeInterface::class);
    }

    function it_supports_channel_pricing_interface(): void
    {
        $this->getSupportedEntity()->shouldReturn(ChannelPricingInterface::class);
    }

    function it_supports_price_and_original_price_fields(): void
    {
        $this->getSupportedFields()->shouldReturn(['price', 'originalPrice']);
    }

    function it_logs_price_change(
        PriceChangeLoggerInterface $priceChangeLogger,
        ChannelPricingInterface $channelPricing
    ): void {
        $priceChangeLogger->log($channelPricing)->shouldBeCalled();

        $this->onChange($channelPricing);
    }
}
