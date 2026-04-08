<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/** @var Block $block */

$fieldName = $block->name()->value();
$options   = array_values(array_filter(array_map('trim', explode("\n", (string) $block->options()->value()))));
$required  = $block->required()->isTrue();

if ($block->label()->isNotEmpty()) : ?>
    <p><strong><?= html($block->label()->value()) ?></strong></p>
<?php endif;

foreach ($options as $index => $option) :
    snippet('form/checkbox', [
        'label'           => $option,
        'id'              => $fieldName . '_' . $index,
        'name'            => $fieldName . '[]',
        'checkboxOrRadio' => 'checkbox',
        'value'           => $option,
        'labelLayout'     => 'small',
        'required'        => $required && $index === 0,
    ]);
endforeach;
