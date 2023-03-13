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

namespace spec\Sylius\PriceHistoryPlugin\Application\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\Doctrine\ORM\ChannelPricingLogEntryRepositoryInterface;

final class ProductLowestPriceBeforeDiscountProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelRepositoryInterface $channelRepository,
    ): void
    {
        $this->beConstructedWith($channelPricingLogEntryRepository, $channelRepository);
    }

    function it_implements_product_lowest_price_processor_interface(): void
    {
        $this->shouldImplement(ProductLowestPriceBeforeDiscountProcessorInterface::class);
    }

    function it_sets_lowest_price_before_discount_to_null_if_original_price_is_null(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelRepositoryInterface $channelRepository,
        ChannelPricingInterface $channelPricing,
    ): void {
        $channelPricing->getOriginalPrice()->willReturn(null);
        $channelPricing->getPrice()->willReturn(2100);

        $channelPricing->setLowestPriceBeforeDiscount(null)->shouldBeCalled();
        $channelPricing->getChannelCode()->shouldNotBeCalled();
        $channelRepository->findOneByCode(Argument::any())->shouldNotBeCalled();
        $channelPricingLogEntryRepository->findLowestPriceBeforeDiscount($channelPricing)->shouldNotBeCalled();

        $this->process($channelPricing);
    }

    function it_sets_lowest_price_before_discount_to_null_if_price_is_equal_original_price(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelRepositoryInterface $channelRepository,
        ChannelPricingInterface $channelPricing,
    ): void {
        $channelPricing->getOriginalPrice()->willReturn(2100);
        $channelPricing->getPrice()->willReturn(2100);

        $channelPricing->setLowestPriceBeforeDiscount(null)->shouldBeCalled();
        $channelPricing->getChannelCode()->shouldNotBeCalled();
        $channelRepository->findOneByCode(Argument::any())->shouldNotBeCalled();
        $channelPricingLogEntryRepository->findLowestPriceBeforeDiscount($channelPricing)->shouldNotBeCalled();

        $this->process($channelPricing);
    }

    function it_sets_lowest_price_before_discount_to_null_if_price_is_greater_than_original_price(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelRepositoryInterface $channelRepository,
        ChannelPricingInterface $channelPricing,
    ): void {
        $channelPricing->getOriginalPrice()->willReturn(2100);
        $channelPricing->getPrice()->willReturn(3700);

        $channelPricing->setLowestPriceBeforeDiscount(null)->shouldBeCalled();
        $channelPricing->getChannelCode()->shouldNotBeCalled();
        $channelRepository->findOneByCode(Argument::any())->shouldNotBeCalled();
        $channelPricingLogEntryRepository->findLowestPriceBeforeDiscount($channelPricing)->shouldNotBeCalled();

        $this->process($channelPricing);
    }

    function it_sets_lowest_price_before_discount_to_null_if_there_is_no_log_entries(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelRepositoryInterface $channelRepository,
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
    ): void {
        $channelPricing->getOriginalPrice()->willReturn(3700);
        $channelPricing->getPrice()->willReturn(2100);
        $channelPricing->getChannelCode()->willReturn('WEB');

        $channelRepository->findOneByCode('WEB')->willReturn($channel);
        $channel->getLowestPriceForDiscountedProductsCheckingPeriod()->willReturn(30);

        $channelPricingLogEntryRepository->findLowestPriceBeforeDiscount($channelPricing, 30)->willReturn(null);

        $channelPricing->setLowestPriceBeforeDiscount(null)->shouldBeCalled();

        $this->process($channelPricing);
    }

    function it_sets_lowest_price_before_discount_to_lowest_price_found_in_the_given_period_if_price_is_less_than_original_price(
        ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository,
        ChannelRepositoryInterface $channelRepository,
        ChannelInterface $channel,
        ChannelPricingInterface $channelPricing,
    ): void {
        $channelPricing->getOriginalPrice()->willReturn(3700);
        $channelPricing->getPrice()->willReturn(2100);
        $channelPricing->getChannelCode()->willReturn('WEB');
        $channelRepository->findOneByCode('WEB')->willReturn($channel);
        $channel->getLowestPriceForDiscountedProductsCheckingPeriod()->willReturn(30);

        $channelPricingLogEntryRepository->findLowestPriceBeforeDiscount($channelPricing, 30)->willReturn(6900);

        $channelPricing->setLowestPriceBeforeDiscount(6900)->shouldBeCalled();

        $this->process($channelPricing);
    }
}
