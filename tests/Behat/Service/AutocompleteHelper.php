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

namespace Tests\Sylius\PriceHistoryPlugin\Behat\Service;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Sylius\Behat\Service\AutocompleteHelper as BaseAutocompleteHelper;
use Sylius\Behat\Service\JQueryHelper;

abstract class AutocompleteHelper extends BaseAutocompleteHelper
{
    public static function removeValue(Session $session, NodeElement $element, string $value): void
    {
        $session->wait(3000, sprintf(
            '$(document.evaluate("%s", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue).dropdown("is visible")',
            str_replace('"', '\"', $element->getXpath()),
        ));

        $elementToRemove = $element->find('css', sprintf('a.ui.label:contains("%s")', $value));
        $elementToRemove->find('css', 'i.delete')->click();

        JQueryHelper::waitForAsynchronousActionsToFinish($session);
    }
}
