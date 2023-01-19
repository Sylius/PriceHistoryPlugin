<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import('vendor/sylius-labs/coding-standard/ecs.php');

    $ecsConfig->parallel();
    $ecsConfig->paths(['src']);
    $ecsConfig->ruleWithConfiguration(PhpdocSeparationFixer::class, ['groups' => [['Given', 'When', 'Then']]]);
    $ecsConfig->ruleWithConfiguration(
        HeaderCommentFixer::class,
        [
            'location' => 'after_open',
            'comment_type' => HeaderCommentFixer::HEADER_COMMENT,
            'header' => <<<TEXT
This file is part of the Sylius package.

(c) Paweł Jędrzejewski

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
TEXT
        ]
    );
};
