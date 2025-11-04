<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('colour-mode snippet: $currentPage not provided');
endif;

/**
 * @var BaseWebPage $currentPage
 */

$colourMode = $currentPage->getColourMode();

?>
<?php if ($colourMode !== 'light') : ?>
    <a href="<?=$currentPage->getUrl()?>?colourMode=light">
<?php else : ?>
    <strong>
<?php endif ?>
    Light
<?php if ($colourMode !== 'light') : ?>
    </a>
<?php else : ?>
    </strong>
<?php endif ?>

    |
<?php if ($colourMode !== 'dark') : ?>
    <a href="<?=$currentPage->getUrl()?>?colourMode=dark">
<?php else : ?>
    <strong>
<?php endif ?>
    Dark
<?php if ($colourMode !== 'dark') : ?>
    </a>
<?php else : ?>
    </strong>
<?php endif ?>
    |
<?php if ($colourMode !== 'auto') : ?>
    <a href="<?=$currentPage->getUrl()?>?colourMode=auto">
<?php else : ?>
    <strong>
<?php endif ?>
    Auto
<?php if ($colourMode !== 'auto') : ?>
    </a>
<?php else : ?>
    </strong>
<?php endif;

