<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin(
    'open-foundations/kirby-base', [
            'blueprints' => require __DIR__ . '/blueprints.php',
            'snippets' => require __DIR__ . '/snippets.php',
            'templates' => [
                'file_link' => __DIR__ . '/templates/file_link.php',
            ]
        ]
);
