<?php

declare(strict_types=1);

if (!isset($image)) :
    return;
endif;

?>


<img
<?php if ($image->hasClass()) : ?>
    class="<?= $image->getClass() ?>"
<?php endif ?>
    alt="<?= $image->getAlt() ?>"
    src="<?= $image->getSrc() ?>"
    width="<?= $image->getWidth() ?>"
    height="<?= $image->getHeight() ?>"
>
