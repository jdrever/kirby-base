<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin(
    'open-foundations/kirby-base', [
            'blueprints' => require __DIR__ . '/blueprints.php',
            'snippets' => require __DIR__ . '/snippets.php',
            'options' => require __DIR__ . '/options.php',
        ]
);
