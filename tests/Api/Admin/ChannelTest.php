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

use Sylius\Bundle\CoreBundle\Application\Kernel;
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
                'locales' => ['/api/v2/admin/locales/en_US'],
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            $this->getLowestPriceVisibleResponseFilename('post', true),
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
                'locales' => ['/api/v2/admin/locales/en_US'],
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
                'lowestPriceForDiscountedProductsVisible' => true,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            $this->getLowestPriceVisibleResponseFilename('post', true),
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
                'locales' => ['/api/v2/admin/locales/en_US'],
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
                'lowestPriceForDiscountedProductsVisible' => false,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            $this->getLowestPriceVisibleResponseFilename('post', false),
            Response::HTTP_CREATED
        );
    }

    /** @test */
    public function it_creates_a_channel_with_default_lowest_price_for_discounted_products_checking_period(): void
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
                'locales' => ['/api/v2/admin/locales/en_US'],
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            $this->getLowestPricePeriodResponseFilename('post', 'default'),
            Response::HTTP_CREATED
        );
    }

    /** @test */
    public function it_creates_a_channel_with_custom_lowest_price_for_discounted_products_checking_period(): void
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
                'locales' => ['/api/v2/admin/locales/en_US'],
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
                'lowestPriceForDiscountedProductsCheckingPeriod' => 10,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            $this->getLowestPricePeriodResponseFilename('post', 'custom'),
            Response::HTTP_CREATED
        );
    }

    /**
     * @test
     * @dataProvider getInvalidPeriod
     */
    public function it_does_not_create_a_channel_with_invalid_lowest_price_for_discounted_products_checking_period(
        mixed $invalidPeriod,
    ): void {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'currency.yaml', 'locale.yaml']);

        $this->client->request(
            method: 'POST',
            uri: '/api/v2/admin/channels',
            server: $this->getLoggedHeader(),
            content: json_encode([
                'name' => 'Web Store',
                'code' => 'WEB',
                'baseCurrency' => '/api/v2/admin/currencies/USD',
                'locales' => ['/api/v2/admin/locales/en_US'],
                'defaultLocale' => '/api/v2/admin/locales/en_US',
                'taxCalculationStrategy' => 'order_items_based',
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
            $this->getLowestPriceVisibleResponseFilename('put', true),
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
            $this->getLowestPriceVisibleResponseFilename('put', false),
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
            $this->getLowestPriceVisibleResponseFilename('put', null),
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_updates_a_channel_with_custom_lowest_price_for_discounted_products_checking_period_field(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'channel.yaml']);

        $this->client->request(
            method: 'PUT',
            uri: sprintf('/api/v2/admin/channels/%s', $fixtures['us_channel']->getCode()),
            server: $this->getLoggedHeader(),
            content: json_encode([
                'lowestPriceForDiscountedProductsCheckingPeriod' => 30,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponse(
            $this->client->getResponse(),
            $this->getLowestPricePeriodResponseFilename('put', 'custom'),
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_does_not_update_the_lowest_price_for_discounted_products_checking_period_field_if_the_field_is_not_provided(): void
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
            $this->getLowestPricePeriodResponseFilename('put', 'no'),
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
            uri: sprintf('/api/v2/admin/channels/%s', $fixtures['eu_channel']->getCode()),
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

    public function getInvalidPeriod(): iterable
    {
        yield [0.1];
        yield [-0.1];
        yield ['10'];
        yield [null];
    }

    private function getLowestPriceVisibleResponseFilename(
        string $httpMethod,
        ?bool $lowestPriceForDiscountedProductsVisible
    ): string {
        return sprintf(
            'admin/%s.%s/%s_channel_with_%s_lowest_price_for_discounted_products_visible_field_response',
            Kernel::MAJOR_VERSION,
            Kernel::MINOR_VERSION,
            $httpMethod,
            null === $lowestPriceForDiscountedProductsVisible
                ? 'no'
                : ($lowestPriceForDiscountedProductsVisible ? 'enabled' : 'disabled'),
        );
    }

    private function getLowestPricePeriodResponseFilename(
        string $httpMethod,
        ?string $lowestPriceForDiscountedProductsCheckingPeriod
    ): string {
        return sprintf(
            'admin/%s.%s/%s_channel_with_%s_lowest_price_for_discounted_products_checking_period_field_response',
            Kernel::MAJOR_VERSION,
            Kernel::MINOR_VERSION,
            $httpMethod,
            $lowestPriceForDiscountedProductsCheckingPeriod ?? 'no',
        );
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
