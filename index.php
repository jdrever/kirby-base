<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin(
    'open/web-base', [
            'blueprints' => require __DIR__ . '/blueprints.php',
        ]
);
