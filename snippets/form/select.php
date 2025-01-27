<?php

declare(strict_types=1);

if (!isset($id)) :
    throw new Exception('select snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('select snippet: $name not provided');
endif;

if (!isset($options)) :
    throw new Exception('select snippet: $options not provided');
endif;

if (!isset($selectedValue)) :
    $selectedValue = '';
endif;

if (!isset($size)) :
    $size = '';
elseif ($size==='small') :
    $size = 'form-select-sm';
endif;

if (isset($label)) : ?>
<label for="<?=$id?>" class="col-form-label"><?=$label?>:</label>
<?php endif ?>

<select name="<?=$name?>" id="<?=$id?>Month" class="form-select <?=$size?>">
<?php foreach ($options as $option)  :
    $selected = (trim($option['value']) === $selectedValue) ? ' selected' : ''; ?>
    <option value="<?=$option['value']?>" <?=$selected?>><?=$option['display']?></option>
<?php endforeach; ?>
</select>