<?php

declare(strict_types=1);

use Tests\Sylius\PriceHistoryPlugin\Application\Kernel;

require_once dirname(__DIR__).'/../../vendor/autoload_runtime.php';

$_SERVER['APP_RUNTIME_OPTIONS'] = ['project_dir' => dirname(__DIR__)];

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
