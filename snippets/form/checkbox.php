<?php

declare(strict_types=1);

if (!isset($id)) :
    throw new Exception('checkbox snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('checkbox snippet: $name not provided');
endif;

if (!isset($value)) :
    throw new Exception('checkbox snippet: $value not provided');
endif;

if (!isset($selectedValue)) :
    $selectedValue = '';
endif;

$labelClass = '';

if (!isset($labelLayout)) :
    $labelLayout = 'normal';
    $labelClass = 'form-check-label';
elseif ($labelLayout === 'small') :
    $labelClass = 'form-check-label fs-6';
elseif ($labelLayout === 'badge') :
    $labelClass = 'badge';
endif;

$checked = (str_contains($selectedValue, $value)) ? 'checked' : '';

if ($labelLayout !== 'badge') : ?>
<div class="form-check">
<?php endif ?>


<input
    type="checkbox"
    name="<?=$name?>"
    id="<?=$id?>"
    value="<?=$value ?>"
<?php if (isset($backgroundColour)) : ?>
    style="background-color: <?=$backgroundColour?>;"
<?php endif ?>
    class="form-check-input p-2 m-1"
    <?=$checked ?>
>
<?php if (isset($label)) :
    if (!isset($labelTitle)) :
        $labelTitle = '';
    endif;
?>
<label
    for="<?=$id?>"
    <?php if (isset($backgroundColour)) : ?>
    style="background-color: <?=$backgroundColour?>;"
    <?php endif ?>
    title="<?=$labelTitle?>"
    class="p-2 m-1 <?=$labelClass?>"
>
    <?=$label ?>
</label>
<?php if (isset($description)) : ?>
    <small id="helpBlock" class="form-text text-muted">
        <?=$description?>
    </small>
    <?php endif;
endif;
if ($labelLayout !== 'badge') : ?>
</div>
<?php endif ?>
