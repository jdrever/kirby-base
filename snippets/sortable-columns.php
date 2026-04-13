<?php

declare(strict_types=1);

use BSBI\WebBase\models\BaseList;

/**
 * Renders a sortable <th> element for a table column.
 *
 * Parameters:
 *   string    $label      Display label for the column header
 *   string    $columnKey  The sort key for this column (must match a value in $list->getSortableColumns() to be sortable)
 *   BaseList  $list       The model list, carrying current sort state and allowed sortable columns
 */

if (!isset($label) || !isset($columnKey) || !isset($list)) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('sortable-columns snippet requires $label, $columnKey, and $list');
}

/** @var BaseList $list */
/** @var string $label */
/** @var string $columnKey */

$isSortable   = in_array($columnKey, $list->getSortableColumns(), true);
$currentSortBy  = $list->getSortBy();
$currentSortDir = $list->getSortDirection();
$isCurrent    = ($currentSortBy === $columnKey);
$nextDir      = ($isCurrent && $currentSortDir === 'asc') ? 'desc' : 'asc';

// Build the sort URL: strip page/sort_by/sort_dir from current URL, then append fresh values.
// Stripping 'page' resets pagination to page 1 when a new sort is chosen.
$currentUrl = (string) kirby()->request()->url();
$baseUrl = preg_replace('/([?&])(page|sort_by|sort_dir)=[^&]*(&|$)/', '$1', $currentUrl) ?? $currentUrl;
$baseUrl = preg_replace('/[?&]$/', '', $baseUrl) ?? $baseUrl;
$separator  = str_contains($baseUrl, '?') ? '&' : '?';
$sortUrl    = $baseUrl . $separator . 'sort_by=' . urlencode($columnKey) . '&sort_dir=' . urlencode($nextDir);
?>
<th>
    <?php if ($isSortable) : ?>
        <a href="<?= htmlspecialchars($sortUrl, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            <?php if ($isCurrent) : ?>
                <?php if ($currentSortDir === 'asc') : ?>
                    <i class="bi bi-arrow-up" aria-label="sorted ascending"></i>
                <?php else : ?>
                    <i class="bi bi-arrow-down" aria-label="sorted descending"></i>
                <?php endif ?>
            <?php else : ?>
                <i class="bi bi-arrow-down-up text-muted" aria-label="sortable"></i>
            <?php endif ?>
        </a>
    <?php else : ?>
        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
    <?php endif ?>
</th>
