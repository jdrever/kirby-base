<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/classes',
        __DIR__ . '/index.php',
        __DIR__ . '/blueprints.php',
        __DIR__ . '/hooks.php',
        __DIR__ . '/routes.php',
        __DIR__ . '/snippets.php',
    ])
    ->withSkipPath(__DIR__ . '/vendor')
    ->withRules([
        NullToStrictStringFuncCallArgRector::class,
    ])
;
