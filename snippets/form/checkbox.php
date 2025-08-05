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

if (!isset($labelLayout)) :
    $labelLayout = '';
elseif ($labelLayout === 'badge') :
    $labelLayout = 'class="badge"';
endif;

$checked = (str_contains($selectedValue, $value) || $selectedValue === '') ? 'checked' : ''; ?>

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
        <?=$labelLayout?>
    <?php if (isset($backgroundColour)) : ?>
        style="background-color: <?=$backgroundColour?>;"
    <?php endif ?>
        title="<?=$labelTitle?>"
        class="p-2 m-1"
>
    <?=$label ?>
</label>
<?php if (isset($description)) : ?>
    <small id="helpBlock" class="form-text text-muted">
        <?=$description?>
    </small>
    <?php endif;
endif ?>