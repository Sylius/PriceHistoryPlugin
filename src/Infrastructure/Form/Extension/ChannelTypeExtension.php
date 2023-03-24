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

namespace Sylius\PriceHistoryPlugin\Infrastructure\Form\Extension;

use Sylius\Bundle\ChannelBundle\Form\Type\ChannelType;
use Sylius\PriceHistoryPlugin\Infrastructure\Form\Type\ChannelPriceHistoryConfigType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ChannelTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('channelPriceHistoryConfig', ChannelPriceHistoryConfigType::class, [
            'label' => false,
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChannelType::class];
    }
}
