<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

/** @noinspection PhpUndefinedMethodInspection */
$fullWidth = $block->fullWidth()->toBool();

/** @noinspection PhpUndefinedMethodInspection */
$rows = $block->rows()->toStructure();
if ($rows->isNotEmpty()) :
    snippet('base/full-width-block-starts', ['fullWidth' => $fullWidth]);?>
    <table class="table">
        <tr>
            <th><?= /** @noinspection PhpUndefinedMethodInspection */
                $block->col1Title()->kt() ?></th>
            <th><?= /** @noinspection PhpUndefinedMethodInspection */
                $block->col2Title()->kt() ?></th>
            <th><?= /** @noinspection PhpUndefinedMethodInspection */
                $block->col3Title()->kt() ?></th>
        </tr>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <td><?= $row->col1()->kt() ?></td>
                <td><?= $row->col2()->kt() ?></td>
                <td><?= $row->col3()->kt() ?></td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif;

snippet('base/full-width-block-ends', ['fullWidth' => $fullWidth]);