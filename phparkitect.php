<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\Extend;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\IsFinal;
use Arkitect\Expression\ForClasses\NotDependsOnTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;
use PhpSpec\ObjectBehavior;

return static function (Config $config): void {
    $specSet = ClassSet::fromDir(__DIR__ . '{/spec}');

    $config->add(
        $specSet,
        Rule::allClasses()
            ->that(new Extend(ObjectBehavior::class))
            ->should(new HaveNameMatching('*Spec'))
            ->because('This is a convention from PHPSpec')
        ,
        Rule::allClasses()
            ->that(new Extend(ObjectBehavior::class))
            ->should(new IsFinal())
            ->because('Specifications should not be extendable')
        ,
    );

    $architectureSet = ClassSet::fromDir(__DIR__ . '{/src}');

    $config->add(
        $architectureSet,
        Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('Sylius\PriceHistory\Domain'))
            ->should(new NotDependsOnTheseNamespaces(
                'Sylius\PriceHistory\Application',
                'Sylius\PriceHistory\Infrastructure',
            ))
            ->because('Domain should not depend on Application or Infrastructure')
        ,
        Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('Sylius\PriceHistory\Application'))
            ->should(new NotDependsOnTheseNamespaces(
                'Sylius\PriceHistory\Infrastructure',
            ))
            ->because('Application should not depend on Infrastructure')
        ,
    );
};
