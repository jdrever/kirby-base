<?php

declare(strict_types=1);

use BSBI\WebBase\helpers\AssetVersioner;

/** @var \BSBI\WebBase\models\BaseWebPage $currentPage */
$cssFile = isset($currentPage) ? $currentPage->getCssFile() : 'custom.css';
$cssUrl = (new AssetVersioner(kirby()->root('index')))->versioned('/assets/css/' . $cssFile);
?>
<link href="<?= htmlspecialchars($cssUrl) ?>" rel="stylesheet" >
