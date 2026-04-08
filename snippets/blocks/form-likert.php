<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/** @var Block $block */

snippet('form/likert', [
    'name'        => $block->name()->value(),
    'label'       => $block->label()->value(),
    'leftLabel'   => $block->leftLabel()->isNotEmpty() ? $block->leftLabel()->value() : 'Strongly disagree',
    'middleLabel' => $block->middleLabel()->value(),
    'rightLabel'  => $block->rightLabel()->isNotEmpty() ? $block->rightLabel()->value() : 'Strongly agree',
    'required'    => $block->required()->isTrue(),
]);
