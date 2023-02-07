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

namespace Tests\Sylius\PriceHistoryPlugin\Api;

use ApiTestCase\JsonApiTestCase as BaseJsonApiTestCase;
use Tests\Sylius\PriceHistoryPlugin\Api\Utils\AdminUserLoginTrait;

abstract class JsonApiTestCase extends BaseJsonApiTestCase
{
    use AdminUserLoginTrait;

    private const CONTENT_TYPE_HEADER = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];

    protected function getUnloggedHeader(): array
    {
        return self::CONTENT_TYPE_HEADER;
    }

    protected function getLoggedHeader(): array
    {
        $token = $this->logInAdminUser('api@example.com');
        $authorizationHeader = self::$kernel->getContainer()->getParameter('sylius.api.authorization_header');
        $header['HTTP_' . $authorizationHeader] = 'Bearer ' . $token;

        return array_merge($header, self::CONTENT_TYPE_HEADER);
    }
}
