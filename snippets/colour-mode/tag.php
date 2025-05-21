<?php

declare(strict_types=1);

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

?>
data-bs-theme="<?=htmlspecialchars($currentPage->getColourMode(), ENT_QUOTES, 'UTF-8')?>"