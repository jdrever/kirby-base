<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/** @var Block $block */

snippet('form/textarea', [
    'id'       => $block->name()->value(),
    'name'     => $block->name()->value(),
    'label'    => $block->label()->value(),
    'required' => $block->required()->isTrue(),
]);
