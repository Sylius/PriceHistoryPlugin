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

namespace Sylius\PriceHistoryPlugin\Fixture\Factory;

use Faker\Factory;
use Faker\Generator;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelPricingLogEntryExampleFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    private const MIN_PRICE = 100;

    private const MAX_PRICE = 10000;

    private Generator $faker;

    private OptionsResolver $optionsResolver;

    public function __construct(private RepositoryInterface $channelPricingRepository)
    {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    public function create(array $options = []): ChannelPricingInterface
    {
        $options = $this->optionsResolver->resolve($options);

        $price = $this->faker->numberBetween(self::MIN_PRICE, self::MAX_PRICE);
        $originalPrice = $this->faker->numberBetween(self::MIN_PRICE, self::MAX_PRICE);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $options['channel_pricing'];
        $channelPricing->setPrice($price);
        $channelPricing->setOriginalPrice($originalPrice >= $price ? $originalPrice : null);

        return $channelPricing;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('channel_pricing', LazyOption::randomOne($this->channelPricingRepository))
            ->setAllowedTypes('channel_pricing', 'object')
        ;
    }
}
