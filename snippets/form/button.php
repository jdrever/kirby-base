<?php

declare(strict_types=1);

if (!isset($name)) :
    $name='submit';
endif;

if (!isset($value)) :
    $value='Submit';
endif;

if (!isset($class)) :
    $class = 'btn btn-success btn-lg';
endif; ?>

<input type="submit" name="<?=$name?>" value="<?=$value?>" class="<?=$class?>">