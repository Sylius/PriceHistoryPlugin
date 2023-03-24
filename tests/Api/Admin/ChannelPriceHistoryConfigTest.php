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

use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\PriceHistoryPlugin\Api\JsonApiTestCase;

final class ChannelPriceHistoryConfigTest extends JsonApiTestCase
{
    /** @test */
    public function it_updates_with_enabled_lowest_price_for_discounted_products_visible_field(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['us_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsVisible' => true,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel_price_history_config/put_with_enabled_lowest_price_for_discounted_products_visible_field',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_updates_a_channel_with_disabled_lowest_price_for_discounted_products_visible_field(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['eu_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsVisible' => false,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel_price_history_config/put_with_disabled_lowest_price_for_discounted_products_visible_field',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_does_not_update_the_lowest_price_for_discounted_products_visible_field_if_the_field_is_not_provided(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['eu_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel_price_history_config/put_with_no_lowest_price_for_discounted_products_visible_field',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_updates_a_channel_with_custom_lowest_price_for_discounted_products_checking_period_field(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['us_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsCheckingPeriod' => 30,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel_price_history_config/put_with_custom_lowest_price_for_discounted_products_checking_period_field',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_does_not_update_the_lowest_price_for_discounted_products_checking_period_field_if_the_field_is_not_provided(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['eu_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel_price_history_config/put_with_no_lowest_price_for_discounted_products_checking_period_field',
            Response::HTTP_OK
        );
    }

    /**
     * @test
     * @dataProvider getInvalidPeriod
     */
    public function it_does_not_update_the_lowest_price_for_discounted_products_checking_period_field_if_the_value_is_invalid(
        mixed $invalidPeriod,
    ): void {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['eu_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsCheckingPeriod' => $invalidPeriod,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertStringContainsString(
            $this->getTypeBasedPeriodValidationMessage($invalidPeriod),
            $response->getContent(),
        );
    }

    /** @test */
    public function it_updates_a_channel_with_taxons_excluded_from_showing_lowest_price(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'taxonomy.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channel-price-history-configs/%d', $fixtures['us_price_history_config']->getId()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'taxonsExcludedFromShowingLowestPrice' => [
                    sprintf('/api/v2/admin/taxons/%s', $fixtures['brand_taxon']->getCode()),
                ],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel_price_history_config/put_with_taxons_excluded_from_showing_lowest_price',
            Response::HTTP_OK,
        );
    }

    public function getInvalidPeriod(): iterable
    {
        yield [0.1];
        yield [-0.1];
        yield ['10'];
        yield [null];
    }

    private function getTypeBasedPeriodValidationMessage(mixed $value): string
    {
        return match (gettype($value)) {
            'double', 'string' => 'lowestPriceForDiscountedProductsCheckingPeriod: This value should be of type int',
            'NULL' => 'lowestPriceForDiscountedProductsCheckingPeriod: This value should not be null',
            default => throw new \InvalidArgumentException(sprintf('Invalid type "%s"', gettype($value))),
        };
    }
}
