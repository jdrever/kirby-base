<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/** @var ResolvedFormField $field */

?>
<p><strong><?= html($field->label) ?></strong></p>
<?php if ($field->help !== '') : ?><div class="form-text mb-2"><?= $field->help ?></div><?php endif; ?>
<?php foreach ($field->options as $index => $option) :
    snippet('form/checkbox', [
        'label'           => $option,
        'id'              => $field->name . '_' . $index,
        'name'            => $field->name,
        'checkboxOrRadio' => 'radio',
        'value'           => $option,
        'labelLayout'     => 'small',
    ]);
endforeach;
