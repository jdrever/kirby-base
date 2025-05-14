<?php

declare(strict_types=1);

use BSBI\Docs\models\SearchPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not provided');
endif;

if (!$currentPage instanceof SearchPage) :
    throw new Exception('$currentPage not instance of SearchPage');
endif;


$query = $currentPage->getQuery();
?>

<h1>Search</h1>

<form method="get" action="/search" role="search">
    <p>Searching for:
        <input type="search" aria-label="Search" name="q" class="form-control-sm ms-2 me-2" value="<?=$query ?>">
        <button class="btn btn-sm btn-success me-1" type="submit">Search Again</button></p>
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
    <?php snippet('search/pagination', ['pagination' => $searchResults->getPagination()]);
else : ?>
    <p>No results found.</p>
<?php endif ?>
