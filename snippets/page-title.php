<?php

declare(strict_types=1);

use models\WebPage;

if (!isset($currentPage)) :
    throw new Exception('page-title snippet: $currentPage not provided');
endif;

$textCentre = isset($centreText) && $centreText === false ? '' : ' class="text-center"';

/** @var WebPage $currentPage */ ?>
<h1<?=$textCentre?>><?= $currentPage->getTitle() ?></h1>