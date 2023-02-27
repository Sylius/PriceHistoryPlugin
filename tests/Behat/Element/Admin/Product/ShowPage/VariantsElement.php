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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Element\Admin\Product\ShowPage;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Element\Element;

final class VariantsElement extends Element implements VariantsElementInterface
{
    public function hasProductVariantWithLowestPriceBeforeDiscountInChannel(
        string $productVariantName,
        string $lowestPriceBeforeDiscount,
        string $channelName,
    ): bool {
        /** @var NodeElement[] $variantRows */
        $variantRows = $this->getDocument()->findAll('css', '#variants .variants-accordion__title');

        /** @var NodeElement $variant */
        foreach ($variantRows as $variant) {
            if ($this->hasProductWithGivenNameCodePriceAndCurrentStock(
                $variant,
                $productVariantName,
                $lowestPriceBeforeDiscount,
                $channelName,
            )) {
                return true;
            }
        }

        return false;
    }

    private function hasProductWithGivenNameCodePriceAndCurrentStock(
        NodeElement $variant,
        string $name,
        string $lowestPriceBeforeDiscount,
        string $channel,
    ): bool {
        $variantContent = $variant->getParent()->find(
            'css',
            sprintf(
                '.variants-accordion__content.%s',
                explode(' ', $variant->getAttribute('class'))[1],
            ),
        );

        return
            $variant->find('css', '.content .variant-name')->getText() === $name &&
            $variantContent->find('css', sprintf('tr.pricing:contains("%s") td:nth-child(4)', $channel))->getText() === $lowestPriceBeforeDiscount
        ;
    }
}
