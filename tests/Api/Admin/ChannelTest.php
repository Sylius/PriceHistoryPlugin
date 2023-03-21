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
    public function it_creates_a_channel_with_default_channel_price_history_config_when_no_additional_data_has_been_passed(): void
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

        $channelPostResponse = $this->client->getResponse();

        $this->assertResponse(
            $channelPostResponse,
            $this->getPostChannelResponseFilename(),
            Response::HTTP_CREATED
        );

        $this->client->request(
            method: 'GET',
            uri: $this->getPriceHistoryConfigUri($channelPostResponse),
            server: $this->getLoggedHeader(),
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel/default_price_history_config',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_creates_a_channel_with_custom_channel_price_history_config(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'taxonomy.yaml']);

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
                'channelPriceHistoryConfig' => [
                    'lowestPriceForDiscountedProductsCheckingPeriod' => 15,
                    'lowestPriceForDiscountedProductsVisible' => false,
                    'taxonsExcludedFromShowingLowestPrice' => [
                        sprintf('/api/v2/admin/taxons/%s', $fixtures['brand_taxon']->getCode()),
                        sprintf('/api/v2/admin/taxons/%s', $fixtures['mug_taxon']->getCode()),
                    ]
                ],
            ], JSON_THROW_ON_ERROR)
        );

        $channelPostResponse = $this->client->getResponse();

        $this->assertResponse(
            $channelPostResponse,
            $this->getPostChannelResponseFilename(),
            Response::HTTP_CREATED
        );

        $this->client->request(
            method: 'GET',
            uri: $this->getPriceHistoryConfigUri($channelPostResponse),
            server: $this->getLoggedHeader(),
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/channel/custom_price_history_config',
            Response::HTTP_OK
        );
    }

    /**
     * @test
     * @dataProvider getInvalidPeriod
     */
    public function it_does_not_allow_creating_a_channel_when_invalid_period_has_been_passed_to_price_history_config(
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
                'channelPriceHistoryConfig' => [
                    'lowestPriceForDiscountedProductsCheckingPeriod' => $invalidPeriod,
                    'lowestPriceForDiscountedProductsVisible' => false,
                    'taxonsExcludedFromShowingLowestPrice' => []
                ],
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

    private function getPostChannelResponseFilename(): string
    {
        return sprintf(
            'admin/channel/%s.%s/post',
            Kernel::MAJOR_VERSION,
            Kernel::MINOR_VERSION,
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

    private function getPriceHistoryConfigUri(Response $response): string
    {
        $content = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return (string) $content['channelPriceHistoryConfig'];
    }
}
