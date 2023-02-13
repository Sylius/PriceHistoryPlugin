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

namespace Tests\Sylius\PriceHistoryPlugin\Api\Shop;

use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\PriceHistoryPlugin\Api\JsonApiTestCase;

final class ProductVariantsTest extends JsonApiTestCase
{
    /** @test */
    public function it_gets_product_variant_with_discount(): void
    {
        $this->loadFixturesFromFile('product_variant.yaml');

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/shop/product-variants/MUG_BLUE',
            parameters: ['_channel_code' => 'HOME'],
            server: $this->getUnloggedHeader()
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'shop/product/get_product_variant_with_discount_response',
            Response::HTTP_OK,
        );
    }

    /** @test */
    public function it_gets_product_variant_without_discount(): void
    {
        $this->loadFixturesFromFile('product_variant_without_discount.yaml');

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/shop/product-variants/MUG_BLUE',
            parameters: ['_channel_code' => 'HOME'],
            server: $this->getUnloggedHeader()
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'shop/product/get_product_variant_without_discount_response',
            Response::HTTP_OK,
        );
    }

    /** @test */
    public function it_gets_product_variant_with_no_prices(): void
    {
        $this->loadFixturesFromFile('product_variant_with_no_prices.yaml');

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/shop/product-variants/MUG_BLUE',
            parameters: ['_channel_code' => 'HOME'],
            server: $this->getUnloggedHeader()
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'shop/product/get_product_variant_with_no_prices',
            Response::HTTP_OK,
        );
    }
}
