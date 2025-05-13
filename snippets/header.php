<?php

declare(strict_types=1);

use Kirby\Template\Slots;

if (!isset($currentPage)) :
    throw new Exception('footer snippet: $currentPage not provided');
endif;

/**
 * @var Slots $slots
 **/

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?=htmlspecialchars($currentPage->getColourMode(), ENT_QUOTES, 'UTF-8')?>">
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

    <script>
        ;(function () {
            const htmlElement = document.querySelector("html")
            if(htmlElement.getAttribute("data-bs-theme") === 'auto') {
                function updateTheme() {
                    document.querySelector("html").setAttribute("data-bs-theme",
                        window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light")
                }
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateTheme)
                updateTheme()
            }
        })()
    </script>
<?php /** @noinspection PhpUndefinedMethodInspection */
if ($lowerHead = $slots->lowerHead()) : ?>
        <?= $lowerHead ?>
<?php endif ?>

    <link href="/assets/css/custom.css" rel="stylesheet" >
    <!-- Fathom - beautiful, simple website analytics -->
    <script src="https://cdn.usefathom.com/script.js" data-site="GBVRYUKF" defer></script>
    <!-- / Fathom -->
</head>

<body>
<a class="skip-link" href="#skipToContent">Skip to Content</a>


