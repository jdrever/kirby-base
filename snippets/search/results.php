<?php

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not provided');
endif;

/** @var BaseWebPage $currentPage */

if (!method_exists($currentPage, 'hasSearchResults')) :
    throw new Exception('$currentPage must implement hasSearchResults method');
endif;

if (!method_exists($currentPage, 'getSearchResults')) :
    throw new Exception('$currentPage must implement getSearchResults method');
endif;

$query = $currentPage->getQuery();
?>
<form method="get" action="/search" role="search">
    <p>Search for:
        <input type="search" aria-label="Search" name="q" class="form-control-sm ms-2 me-2" value="<?=$query ?>">
        <button class="btn btn-sm btn-success me-1" type="submit">Search</button></p>
</form>
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
