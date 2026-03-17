<?php
/** @var \BSBI\WebBase\models\BaseWebPage $currentPage */
$cssFile = isset($currentPage) ? $currentPage->getCssFile() : 'custom.css';
?>
<link href="/assets/css/<?= htmlspecialchars($cssFile) ?>" rel="stylesheet" >