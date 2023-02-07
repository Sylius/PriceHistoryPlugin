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

use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Model\ChannelPricingLogEntryInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\PriceHistoryPlugin\Api\JsonApiTestCase;

final class ChannelTest extends JsonApiTestCase
{
    /** @test */
    public function it_creates_a_channel_with_default_lowest_price_for_discounted_products_visible_field(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'currency.yaml', 'locale.yaml']);

        $this->client->request(
            method: 'POST',
            uri: '/api/v2/admin/channels',
            server: $this->getLoggedHeader(),
            content: json_encode([
                'name' => 'Web Store',
                'code' => 'WEB',
                'baseCurrency' => '/api/v2/admin/currencies/USD',
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
                'shippingAddressInCheckoutRequired' => true,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/post_channel_with_enabled_lowest_price_for_discounted_products_visible_field_response',
            Response::HTTP_CREATED
        );
    }

    /** @test */
    public function it_creates_a_channel_with_enabled_lowest_price_for_discounted_products_visible_field(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'currency.yaml', 'locale.yaml']);

        $this->client->request(
            method: 'POST',
            uri: '/api/v2/admin/channels',
            server: $this->getLoggedHeader(),
            content: json_encode([
                'name' => 'Web Store',
                'code' => 'WEB',
                'baseCurrency' => '/api/v2/admin/currencies/USD',
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
                'shippingAddressInCheckoutRequired' => true,
                'lowestPriceForDiscountedProductsVisible' => true,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/post_channel_with_enabled_lowest_price_for_discounted_products_visible_field_response',
            Response::HTTP_CREATED
        );
    }

    /** @test */
    public function it_creates_a_channel_with_disabled_lowest_price_for_discounted_products_visible_field(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'currency.yaml', 'locale.yaml']);

        $this->client->request(
            method: 'POST',
            uri: '/api/v2/admin/channels',
            server: $this->getLoggedHeader(),
            content: json_encode([
                'name' => 'Web Store',
                'code' => 'WEB',
                'baseCurrency' => '/api/v2/admin/currencies/USD',
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
                'shippingAddressInCheckoutRequired' => true,
                'lowestPriceForDiscountedProductsVisible' => false,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/post_channel_with_disabled_lowest_price_for_discounted_products_visible_field_response',
            Response::HTTP_CREATED
        );
    }

    /** @test */
    public function it_updates_a_channel_with_enabled_lowest_price_for_discounted_products_visible_field(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channels/%s', $fixtures['us_channel']->getCode()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsVisible' => true,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/put_channel_with_enabled_lowest_price_for_discounted_products_visible_field_response',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_updates_a_channel_with_disabled_lowest_price_for_discounted_products_visible_field(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channels/%s', $fixtures['eu_channel']->getCode()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsVisible' => false,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/put_channel_with_disabled_lowest_price_for_discounted_products_visible_field_response',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_does_not_update_the_lowest_price_for_discounted_products_visible_field_if_the_field_is_not_provided(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channels/%s', $fixtures['eu_channel']->getCode()),
            server: $this->getLoggedHeader(),
            content: json_encode([], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/put_channel_with_no_lowest_price_for_discounted_products_visible_field_response',
            Response::HTTP_OK
        );
    }
}
