<?php

declare(strict_types=1);

use Kirby\Template\Slots;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/**
 * @var Slots $slots
 **/

?>
<!DOCTYPE html>
<html lang="en" <?php snippet('colour-mode/tag') ?>>
<head>
    <meta charset="utf-8">
    <title><?=$currentPage->getTitle() ?></title>
    <?php if ($currentPage->hasDescription()) : ?>
    <meta name="description" content="<?=$currentPage->getDescription()?>">
    <?php endif ?>
    <meta name="author" content="<?=$currentPage->getAuthors()?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="<?= $currentPage->getOpenGraphTitle() ?>" />
    <meta property="og:description" content="<?= $currentPage->getOpenGraphDescription() ?>" />
    <meta property="og:image" content="<?= $currentPage->getOpenGraphImage() ?>" />
    <meta property="og:url" content="<?= $currentPage->getUrl() ?>" />
    <meta property="og:type" content="website" />
    <?php snippet('colour-mode/script') ?>
    <?php snippet('base/styles') ?>
<?php /** @noinspection PhpUndefinedMethodInspection */
if ($lowerHead = $slots->lowerHead()) : ?>
        <?= $lowerHead ?>
<?php endif ?>
</head>

<body>
<?php snippet('skip-to-content') ?>


