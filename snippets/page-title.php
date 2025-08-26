<?php

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('page-title snippet: $currentPage not provided');
endif;

/** @var BaseWebPage $currentPage */ ?>

$textCentre = isset($centreText) && $centreText === false ? '' : ' class="text-center"';


<h1<?=$textCentre?>><?= $currentPage->getDisplayPageTitle() ?></h1>