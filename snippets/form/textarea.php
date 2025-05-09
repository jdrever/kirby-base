<?php

declare(strict_types=1);

if (!isset($id)) :
    throw new Exception('textarea snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('textarea snippet: $name not provided');
endif;

if (!isset($value)) :
    throw new Exception('textarea snippet: $value not provided');
endif;

if (!isset($required)) :
    $required = false;
endif;

if (!isset($rows)) :
    $rows = 5;
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
<textarea
    type="textbox"
    name="<?=$name?>"
    id="<?=$id?>"
    rows="<?=$rows?>"
    class="form-control p-2 m-1<?= !empty($alert) ? ' is-invalid' : '' ?>"
    <?= $isRequired ?>
><?= esc($value, 'attr') ?></textarea>
<?= !empty($alert) ? '<span class="invalid-feedback">' . esc($alert) . '</span>' : '' ?>