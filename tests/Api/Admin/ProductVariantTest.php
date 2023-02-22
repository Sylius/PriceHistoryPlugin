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

namespace Tests\Sylius\PriceHistoryPlugin\Api\Admin;

use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\PriceHistoryPlugin\Api\JsonApiTestCase;

final class ProductVariantTest extends JsonApiTestCase
{
    /** @test */
    public function it_gets_product_variant_with_discount_in_home_channel_and_without_discount_in_fashion_channel(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant.yaml']);

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/admin/product-variants/MUG_BLUE',
            server: $this->getLoggedHeader(),
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/product/get_product_variant_with_discount_in_home_channel_and_without_discount_in_fashion_channel_response',
            Response::HTTP_OK,
        );
    }

    /** @test */
    public function it_gets_product_variant_with_no_prices(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant_with_no_prices.yaml']);

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/admin/product-variants/MUG_BLUE',
            server: $this->getLoggedHeader(),
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/product/get_product_variant_with_no_prices',
            Response::HTTP_OK,
        );
    }

    /** @test */
    public function it_does_not_allow_to_write_lowest_price_before_discount_field_when_creating(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant.yaml']);

        $this->client->request(
            method: 'POST',
            uri: '/api/v2/admin/product-variants',
            server: $this->getLoggedHeader(),
            content: json_encode([
                'code' => 'MUG_GREEN',
                'product' => '/api/v2/admin/products/MUG',
                'channelPricings' => [
                    'HOME' => [
                        'channelCode' => 'HOME',
                        'price' => 3000,
                        'originalPrice' => 4000,
                        'lowestPriceBeforeDiscount' => 7777,
                    ],
                    'FASHION' => [
                        'channelCode' => 'FASHION',
                        'price' => 4000,
                        'originalPrice' => 5000,
                        'lowestPriceBeforeDiscount' => 8888,
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/product/post_product_variant_with_lowest_price_before_discount_response',
            Response::HTTP_CREATED,
        );
    }

    /** @test */
    public function it_does_not_allow_to_write_lowest_price_before_discount_field_when_updating(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant.yaml']);

        /** @var ChannelPricingInterface $homeChannelPricing */
        $homeChannelPricing = $fixtures['channel_pricing_product_variant_mug_blue_home'];

        /** @var ChannelPricingInterface $fashionChannelPricing */
        $fashionChannelPricing = $fixtures['channel_pricing_product_variant_mug_blue_fashion'];

        $this->client->request(
            method: 'PUT',
            uri: '/api/v2/admin/product-variants/MUG_BLUE',
            server: $this->getLoggedHeader(),
            content: json_encode([
                'channelPricings' => [
                    'HOME' => [
                        '@id' => sprintf('/api/v2/admin/channel-pricings/%s', $homeChannelPricing->getId()),
                        'price' => 3000,
                        'originalPrice' => 4000,
                        'lowestPriceBeforeDiscount' => 7777,
                    ],
                    'FASHION' => [
                        '@id' => sprintf('/api/v2/admin/channel-pricings/%s', $fashionChannelPricing->getId()),
                        'price' => 4000,
                        'originalPrice' => 5000,
                        'lowestPriceBeforeDiscount' => 8888,
                    ],
                ]
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/product/put_product_variant_with_lowest_price_before_discount_response',
            Response::HTTP_OK,
        );
    }
}
