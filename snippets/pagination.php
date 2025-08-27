<?php

declare(strict_types=1);


use BSBI\WebBase\models\Pagination;

if (!isset($pagination)) :
    return;
endif;

$currentPage = $pagination->getCurrentPage();
$pageCount = $pagination->getPageCount();
$limit = 3;
$showEllipsisStart = false;
$showEllipsisEnd = false;

/** @var Pagination $pagination **/
?>

<nav aria-label="Page navigation">
    <ul class="pagination">
        <?php if ($pagination->hasPreviousPage()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pagination->getPreviousPageUrl() ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo;</span>
            </li>
        <?php endif;
        $lastPrintedPage = 0;
        for ($i = 1; $i <= $pagination->getPageCount(); $i++) :
            // Determine if the current page number should be displayed
            $showPage = false;
            // Always show the first `limit` pages
            if ($i <= $limit) :
                $showPage = true;
            // Always show the last `limit` pages
            elseif ($i > $pageCount - $limit) :
                $showPage = true;
            // Always show the current page and its direct neighbors
            elseif (abs($i - $currentPage) <= 1) :
                $showPage = true;
            endif;

            // Print the page number if it's within the rules
            if ($showPage) :
                // If there's a gap since the last printed page, show an ellipsis
                if ($i > $lastPrintedPage + 1) :?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif ?>
                <li class="page-item<?= ($currentPage === $i) ? ' active' : '' ?>">
                    <a class="page-link" href="<?= $pagination->getPageURL($i) ?>"><?= $i ?></a>
                </li>
                <?php $lastPrintedPage = $i;
            endif;
        endfor ?>
        <?php if ($pagination->hasNextPage()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pagination->getNextPageUrl() ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
</nav>