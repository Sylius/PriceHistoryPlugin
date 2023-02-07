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

use ApiTestCase\JsonApiTestCase;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Model\ChannelPricingLogEntryInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\PriceHistoryPlugin\Api\Utils\AdminUserLoginTrait;

final class ChannelPricingLogEntryTest extends JsonApiTestCase
{
    use AdminUserLoginTrait;

    private const CONTENT_TYPE_HEADER = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];

    /** @test */
    public function it_denies_access_to_a_channel_pricing_log_entries_for_not_authenticated_user(): void
    {
        $this->loadFixturesFromFiles(['product_variant.yaml']);

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/admin/channel-pricing-log-entries',
            server: self::CONTENT_TYPE_HEADER,
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /** @test */
    public function it_gets_single_channel_pricing_log_entry(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant.yaml']);
        $header = $this->getLoggedHeader();

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $fixtures['channel_pricing_product_variant_mug_blue_home'];

        /** @var RepositoryInterface $channelPricingLogEntryRepository */
        $channelPricingLogEntryRepository = $this->getContainer()->get('sylius_price_history.repository.channel_pricing_log_entry');
        /** @var ChannelPricingLogEntryInterface $firstLogEntry */
        $channelPricingLogEntry = $channelPricingLogEntryRepository->findOneBy(['channelPricing' => $channelPricing]);

        $this->client->request(
            method: 'GET',
            uri: sprintf('/api/v2/admin/channel-pricing-log-entries/%d', $channelPricingLogEntry->getId()),
            server: $header,
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/get_channel_pricing_log_entry_response',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_gets_all_channel_pricing_log_entries(): void
    {
        $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant.yaml']);
        $header = $this->getLoggedHeader();

        $this->client->request(
            method: 'GET',
            uri: '/api/v2/admin/channel-pricing-log-entries',
            server: $header,
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/get_channel_pricing_log_entries_response',
            Response::HTTP_OK
        );
    }

    /** @test */
    public function it_gets_filtered_channel_pricing_log_entries(): void
    {
        $fixtures = $this->loadFixturesFromFiles(['authentication/api_administrator.yaml', 'product_variant.yaml']);
        $header = $this->getLoggedHeader();

        $uri = '/api/v2/admin/channel-pricing-log-entries';
        $uri .= '?channelPricing.channelCode=' . $fixtures['channel_home']->getCode();
        $uri .= '&channelPricing.productVariant.code=' . $fixtures['product_variant_mug_blue']->getCode();

        $this->client->request(
            method: 'GET',
            uri: $uri,
            server: $header,
        );

        $this->assertResponse(
            $this->client->getResponse(),
            'admin/get_filtered_channel_pricing_log_entries_response',
            Response::HTTP_OK
        );
    }

    private function getLoggedHeader(): array
    {
        $token = $this->logInAdminUser('api@example.com');
        $authorizationHeader = self::$kernel->getContainer()->getParameter('sylius.api.authorization_header');
        $header['HTTP_' . $authorizationHeader] = 'Bearer ' . $token;

        return array_merge($header, self::CONTENT_TYPE_HEADER);
    }
}
