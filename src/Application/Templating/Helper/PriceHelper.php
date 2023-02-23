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

namespace Sylius\PriceHistoryPlugin\Application\Templating\Helper;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\PriceHistoryPlugin\Application\Calculator\ProductVariantPriceCalculatorInterface;
use Symfony\Component\Templating\Helper\Helper;
use Webmozart\Assert\Assert;

class PriceHelper extends Helper
{
    public function __construct(protected ProductVariantPriceCalculatorInterface $productVariantPriceCalculator)
    {
    }

    public function getLowestPriceBeforeDiscount(ProductVariantInterface $productVariant, array $context): ?int
    {
        Assert::keyExists($context, 'channel');

        return $this
            ->productVariantPriceCalculator
            ->calculateLowestPriceBeforeDiscount($productVariant, $context)
        ;
    }

    public function hasLowestPriceBeforeDiscount(ProductVariantInterface $productVariant, array $context): bool
    {
        Assert::keyExists($context, 'channel');

        return null !== $this->getLowestPriceBeforeDiscount($productVariant, $context);
    }

    public function getName(): string
    {
        return 'sylius_price_history_calculate_price';
    }
}
