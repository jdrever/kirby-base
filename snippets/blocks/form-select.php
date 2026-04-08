<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/** @var Block $block */

$rawOptions = array_values(array_filter(array_map('trim', explode("\n", (string) $block->options()->value()))));
$options    = array_map(static fn(string $o): array => ['value' => $o, 'display' => $o], $rawOptions);

snippet('form/select', [
    'id'       => $block->name()->value(),
    'name'     => $block->name()->value(),
    'label'    => $block->label()->value(),
    'options'  => $options,
    'required' => $block->required()->isTrue(),
]);
