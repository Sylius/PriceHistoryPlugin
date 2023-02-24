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

namespace Sylius\PriceHistoryPlugin\Infrastructure\Twig;

use Sylius\PriceHistoryPlugin\Application\Templating\Helper\PriceHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class PriceExtension extends AbstractExtension
{
    public function __construct(private PriceHelper $helper)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sylius_has_lowest_price', [$this->helper, 'hasLowestPriceBeforeDiscount']),
            new TwigFilter('sylius_calculate_lowest_price', [$this->helper, 'getLowestPriceBeforeDiscount']),
        ];
    }
}
