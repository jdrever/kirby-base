<?php

declare(strict_types=1);


use BSBI\WebBase\models\Pagination;

if (!isset($pagination)) :
    return;
endif;

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
        <?php endif ?>
        <?php for ($i = 1; $i <= $pagination->getPageCount(); $i++) : ?>
            <li class="page-item<?= ($pagination->getCurrentPage() === $i) ? ' active' : '' ?>">
                <a class="page-link" href="<?= $pagination->getPageURL($i) ?>"><?= $i ?></a>
            </li>
        <?php endfor ?>
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