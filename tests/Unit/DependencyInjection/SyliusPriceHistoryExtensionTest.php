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

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\DoctrineMigrationsExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sylius\PriceHistoryPlugin\DependencyInjection\SyliusPriceHistoryExtension;
use SyliusLabs\DoctrineMigrationsExtraBundle\DependencyInjection\SyliusLabsDoctrineMigrationsExtraExtension;

final class SyliusPriceHistoryExtensionTest extends AbstractExtensionTestCase
{
    /** @test */
    public function it_autoconfigures_prepending_doctrine_migration_with_proper_migrations_paths(): void
    {
        $this->configureContainer();

        $this->container->registerExtension(new DoctrineMigrationsExtension());
        $this->container->registerExtension(new SyliusLabsDoctrineMigrationsExtraExtension());

        $this->load();

        $doctrineMigrationsExtensionConfig = $this->container->getExtensionConfig('doctrine_migrations');

        self::assertArrayHasKey(
            'Sylius\PriceHistoryPlugin\Infrastructure\Migrations',
            $doctrineMigrationsExtensionConfig[0]['migrations_paths']
        );
        self::assertSame(
            '@SyliusPriceHistoryPlugin/src/Infrastructure/Migrations',
            $doctrineMigrationsExtensionConfig[0]['migrations_paths']['Sylius\PriceHistoryPlugin\Infrastructure\Migrations']
        );

        $syliusLabsDoctrineMigrationsExtraExtensionConfig = $this->container
            ->getExtensionConfig('sylius_labs_doctrine_migrations_extra')
        ;

        self::assertArrayHasKey(
            'Sylius\PriceHistoryPlugin\Infrastructure\Migrations',
            $syliusLabsDoctrineMigrationsExtraExtensionConfig[0]['migrations']
        );
        self::assertSame(
            ['Sylius\Bundle\CoreBundle\Migrations'],
            $syliusLabsDoctrineMigrationsExtraExtensionConfig[0]['migrations']['Sylius\PriceHistoryPlugin\Infrastructure\Migrations']
        );
    }

    /** @test */
    public function it_does_not_autoconfigure_prepending_doctrine_migrations_if_it_is_disabled(): void
    {
        $this->configureContainer();
        $this->container->setParameter('sylius_core.prepend_doctrine_migrations', false);

        $this->load();

        $doctrineMigrationsExtensionConfig = $this->container->getExtensionConfig('doctrine_migrations');
        self::assertEmpty($doctrineMigrationsExtensionConfig);

        $syliusLabsDoctrineMigrationsExtraExtensionConfig = $this->container
            ->getExtensionConfig('sylius_labs_doctrine_migrations_extra')
        ;
        self::assertEmpty($syliusLabsDoctrineMigrationsExtraExtensionConfig);
    }

    /** @test */
    public function it_prepends_configuration_with_doctrine_mapping(): void
    {
        $this->container->setParameter('kernel.bundles_metadata', ['SyliusPriceHistoryPlugin' => ['path' => __DIR__ . '../..']]);
        $this->container->prependExtensionConfig('doctrine', ['dbal' => [], 'orm' => []]);

        $this->load();

        $doctrineConfig = $this->container->getExtensionConfig('doctrine')[0];

        $this->assertSame($doctrineConfig['orm']['mappings']['SyliusPriceHistoryPlugin'], [
            'type' => 'xml',
            'dir' => __DIR__ . '../../config/doctrine/',
            'is_bundle' => false,
            'prefix' => 'Sylius\PriceHistoryPlugin\Domain\Model',
            'alias' => 'SyliusPriceHistoryPlugin',
        ]);
    }

    /** @test */
    public function it_prepends_configuration_with_api_platform_mapping(): void
    {
        $this->container->setParameter('kernel.bundles_metadata', ['SyliusPriceHistoryPlugin' => ['path' => __DIR__ . '../..']]);

        $this->load();

        $apiPlatformConfig = $this->container->getExtensionConfig('api_platform')[0];

        $this->assertSame($apiPlatformConfig['mapping']['paths'], [
            __DIR__ . '../../config/api_resources/',
        ]);
    }

    /** @test */
    public function it_loads_batch_size_parameter_value_properly(): void
    {
        $this->configureContainer();

        $this->load(['batch_size' => 200]);

        $this->assertContainerBuilderHasParameter('sylius_price_history.batch_size', 200);
    }

    /** @test */
    public function it_loads_default_batch_size_properly(): void
    {
        $this->configureContainer();

        $this->load();

        $this->assertContainerBuilderHasParameter('sylius_price_history.batch_size', 100);
    }

    protected function getContainerExtensions(): array
    {
        return [new SyliusPriceHistoryExtension()];
    }

    private function configureContainer(): void
    {
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.bundles_metadata', ['SyliusPriceHistoryPlugin' => ['path' => __DIR__ . '../../']]);
    }
}
