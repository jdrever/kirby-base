<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

if (!isset($id)) :
    throw new Exception('textbox snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('textbox snippet: $name not provided');
endif;

if (!isset($value)) :
    $value = '';
endif;

if (!isset($type)) :
    $type = 'text';
endif;

if (!isset($required)) :
    $required = false;
endif;



if (!isset($readOnly)) :
    $readOnly = false;
endif;

$isRequired = ($required) ? 'required' : '';

if (isset($label)) : ?>
    <label
            for="<?=$id?>"
            class="col-form-label"
    >
        <?=$label ?>
    <?php if ($required) : ?>
        <span class="visually-hidden">(required)</span>
        <span aria-hidden="true">*</span>
   <?php endif ?>
    </label>
<?php endif ?>
<input
    type="<?=$type?>"
    name="<?=$name?>"
    id="<?=$id?>"
    value="<?= esc($value, 'attr') ?>"
    class="form-control <?= !empty($alert) ? ' is-invalid' : '' ?>"
    <?= $isRequired ?>
<?php if (isset($pattern)) : ?>
    pattern="<?=$pattern ?>"
<?php endif ?>
    <?= $readOnly ? 'readonly' : '' ?>
>
<?= !empty($alert) ? '<span class="invalid-feedback">' . esc($alert) . '</span>' : '' ?>