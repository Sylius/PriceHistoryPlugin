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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPriceHistoryConfigInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Webmozart\Assert\Assert;

final class ChannelTypeExtension extends AbstractTypeExtension implements DataMapperInterface
{
    public function __construct(private DataMapperInterface $propertyPathDataMapper)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lowestPriceForDiscountedProductsVisible', CheckboxType::class, [
                'label' => 'sylius.form.channel.lowest_price_for_discounted_products_visible',
                'required' => false,
            ])
            ->add('lowestPriceForDiscountedProductsCheckingPeriod', IntegerType::class, [
                'label' => 'sylius.form.channel.period_for_which_the_lowest_price_is_calculated',
            ])
            ->add('taxonsExcludedFromShowingLowestPrice', TaxonAutocompleteChoiceType::class, [
                'label' => 'sylius.ui.taxons_for_which_the_lowest_price_is_not_displayed',
                'required' => false,
                'multiple' => true,
            ])
        ;
        $builder->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $this->propertyPathDataMapper->mapDataToForms($viewData, $forms);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        Assert::isInstanceOf($channel = $viewData, ChannelInterface::class);
        /** @var ChannelPriceHistoryConfigInterface $channelPriceHistoryConfig */
        $channelPriceHistoryConfig = $channel->getChannelPriceHistoryConfig();

        /** @var \Traversable $traversableForms */
        $traversableForms = $forms;
        $forms = iterator_to_array($traversableForms);

        $channelPriceHistoryConfig->clearTaxonsExcludedFromShowingLowestPrice();

        /** @var Collection $excludedTaxons */
        $excludedTaxons = $forms['taxonsExcludedFromShowingLowestPrice']->getData();

        /** @var TaxonInterface $taxon */
        foreach ($excludedTaxons as $taxon) {
            $channelPriceHistoryConfig->addTaxonExcludedFromShowingLowestPrice($taxon);
        }

        unset($forms['taxonsExcludedFromShowingLowestPrice']);

        $this->propertyPathDataMapper->mapFormsToData(new ArrayCollection($forms), $viewData);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChannelType::class];
    }
}
