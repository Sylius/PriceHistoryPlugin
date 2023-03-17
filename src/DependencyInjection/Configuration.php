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

namespace Sylius\PriceHistoryPlugin\DependencyInjection;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Form\Type\DefaultResourceType;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\PriceHistoryPlugin\Domain\Factory\ChannelPricingLogEntryFactory;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPriceHistoryConfig;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPriceHistoryConfigInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingLogEntry;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingLogEntryInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\Doctrine\ORM\ChannelPricingLogEntryRepository;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_price_history');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('batch_size')
                    ->defaultValue(100)
                    ->validate()
                        ->ifTrue(fn (int $batchSize): bool => $batchSize <= 0)
                        ->thenInvalid('Expected value bigger than 0, but got %s.')
                    ->end()
                ->end()
            ->end()
        ;

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('channel_pricing_log_entry')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(ChannelPricingLogEntry::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(ChannelPricingLogEntryInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(ChannelPricingLogEntryRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(ChannelPricingLogEntryFactory::class)->end()
                                        ->scalarNode('form')->defaultValue(DefaultResourceType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('channel_price_history_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(ChannelPriceHistoryConfig::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(ChannelPriceHistoryConfigInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                        ->scalarNode('form')->defaultValue(DefaultResourceType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
