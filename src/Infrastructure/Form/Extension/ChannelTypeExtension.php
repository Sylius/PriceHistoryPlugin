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
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class ChannelTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lowestPriceForDiscountedProductsVisible', CheckboxType::class, [
                'label' => 'sylius_price_history.form.channel.lowest_price_for_discounted_products_visible',
                'required' => false,
            ])
            ->add('lowestPriceForDiscountedProductsCheckingPeriod', IntegerType::class, [
                'label' => 'sylius_price_history.form.channel.period_for_which_the_lowest_price_is_calculated',
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChannelType::class];
    }
}
