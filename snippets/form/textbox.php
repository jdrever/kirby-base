<?php

declare(strict_types=1);

if (!isset($id)) :
    throw new Exception('textbox snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('textbox snippet: $name not provided');
endif;

if (!isset($value)) :
    throw new Exception('textbox snippet: $value not provided');
endif;

if (!isset($required)) :
    $required = false;
endif;

$isRequired = ($required) ? 'required' : '';

if (isset($label)) : ?>
    <label
            for="<?=$id?>"
            class="p-2 m-1"
    >
        <?=$label ?>
    <?php if ($required) : ?>
        <abbr title="required">*</abbr>
   <?php endif ?>
    </label>
<?php endif ?>
<input
    type="textbox"
    name="<?=$name?>"
    id="<?=$id?>"
    value="<?= esc($value, 'attr') ?>"
    class="form-control p-2 m-1"
    <?= $isRequired ?>
>
<?= isset($alert) ? '<span class="alert error">' . esc($alert) . '</span>' : '' ?>