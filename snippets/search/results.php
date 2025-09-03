<?php

declare(strict_types=1);

use Kirby\Template\Slots;

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not provided');
endif;

/**
 * @var Slots $slots
 */

/** @var BaseWebPage $currentPage */

if (!method_exists($currentPage, 'hasSearchResults')) :
    throw new Exception('$currentPage must implement hasSearchResults method');
endif;

if (!method_exists($currentPage, 'getSearchResults')) :
    throw new Exception('$currentPage must implement getSearchResults method');
endif;

$query = $currentPage->getQuery();

snippet('search/form');
?>


<?php if ($additionalSearchForm = $slots->additionalSearchForm()) : ?>
    <?=$additionalSearchForm?>
<?php endif ?>

<?php if ($currentPage->hasSearchResults()) :
    $searchResults = $currentPage->getSearchResults(); ?>
    <ul>
        <?php foreach ($searchResults->getList() as $resultPage) : ?>
            <li>
                <a href="<?= $resultPage->getUrl() ?>?q=<?= $query ?>">
                    <?=$resultPage->getTitle() ?>
                </a>
                <?php if ($resultPage->hasDescription()) : ?>
                    <p style="font-size:0.8em;">
                        <?=$resultPage->getDescription()?>
                    </p>
                <?php endif ?>
            </li>
        <?php endforeach ?>
    </ul>
    <?php snippet('base/pagination', ['pagination' => $searchResults->getPagination()]);
else :
    if (!empty($query)) :?>
    <p>No results found.</p>
    <?php endif;
endif ?>
