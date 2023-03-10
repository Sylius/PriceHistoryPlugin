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

namespace Tests\Sylius\PriceHistoryPlugin\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Sylius\PriceHistoryPlugin\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /** @test */
    public function it_configures_batch_size_to_100_by_default(): void
    {
        $this->assertProcessedConfigurationEquals(
            [[]],
            ['batch_size' => 100],
            'batch_size',
        );
    }

    /** @test */
    public function it_allows_for_assigning_integer_as_batch_size(): void
    {
        $this->assertProcessedConfigurationEquals(
            [['batch_size' => 200]],
            ['batch_size' => 200],
            'batch_size',
        );
    }

    /** @test */
    public function it_throws_an_exception_if_value_of_batch_size_is_lower_then_1(): void
    {
        $this->assertConfigurationIsInvalid(
            [['batch_size' => -1]],
            'Expected value bigger than 0, but got -1.',
        );

        $this->assertConfigurationIsInvalid(
            [['batch_size' => 0]],
            ' Expected value bigger than 0, but got 0.',
        );
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
