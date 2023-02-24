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

use Sylius\Bundle\CoreBundle\Application\Kernel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SyliusVersionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_sylius_equal_or_above_version', [$this, 'isSyliusEqualOrAboveVersion']),
        ];
    }

    public function isSyliusEqualOrAboveVersion(int $version): bool
    {
        /** @psalm-suppress DeprecatedClass */
        return Kernel::VERSION_ID >= $version;
    }
}
