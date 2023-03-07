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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Webmozart\Assert\Assert;

final class TaxonContext implements Context
{
    public function __construct(
        private TaxonRepositoryInterface $taxonRepository,
        private string $locale,
    ) {
    }

    /**
     * @Transform /^"([^"]+)" and "([^"]+)" taxons$/
     */
    public function getTaxonsByNames(...$taxonNames): iterable
    {
        foreach ($taxonNames as $taxonName) {
            yield $this->getTaxonByName($taxonName);
        }
    }

    private function getTaxonByName(string $name): object
    {
        $taxons = $this->taxonRepository->findByName($name, $this->locale);

        Assert::eq(
            count($taxons),
            1,
            sprintf('%d taxons have been found with name "%s".', count($taxons), $name),
        );

        return $taxons[0];
    }
}
