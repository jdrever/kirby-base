<?php

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('page-title snippet: $currentPage not provided');
endif;

$textCentre = isset($centreText) && $centreText === false ? '' : ' class="text-center"';

/** @var BaseWebPage $currentPage */ ?>

<h1<?=$textCentre?>><?= $currentPage->getDisplayPageTitle() ?></h1>