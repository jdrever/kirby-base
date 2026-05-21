<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/** @var ResolvedFormField $field */

?>
<p><strong><?= $field->label ?><?php if ($field->required) : ?><span class="visually-hidden">(required)</span><span aria-hidden="true">*</span><?php endif ?></strong></p>
<?php if ($field->help !== '') : ?><div class="form-text mb-2"><?= $field->help ?></div><?php endif; ?>
<?php foreach ($field->options as $index => $option) :
    snippet('form/checkbox', [
        'label'           => $option,
        'id'              => $field->name . '_' . $index,
        'name'            => $field->name,
        'checkboxOrRadio' => 'radio',
        'value'           => $option,
        'labelLayout'     => 'small',
        'required'        => $field->required && $index === 0,
    ]);
endforeach;
