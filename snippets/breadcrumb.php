<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('breadcrumb snippet: $currentPage not provided');
endif;

/**
 * @var BaseWebPage $currentPage
 */

if (!$currentPage->hasBreadcrumb()) :
    return;
endif;
$breadcrumb = $currentPage->getBreadcrumb();

?>
<div class="breadcrumb d-print-none bg-secondary-subtle">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mx-0 mb-0">
<?php foreach ($breadcrumb->getListItems() as $crumb) : ?>
            <li class="breadcrumb-item">
                <a href="<?= $crumb->getUrl() ?>">
                    <?= $crumb->getTitle()?>
                </a>
            </li>
<?php endforeach ?>
        </ol>
    </nav>
</div>