<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

$rows = $block->rows()->toStructure();
if ($rows->isNotEmpty()) :?>
    <table class="table">
        <tr>
            <th><?= $block->col1Title() ?></th>
            <th><?= $block->col2Title() ?></th>
            <th><?= $block->col3Title() ?></th>
            <th><?= $block->col4Title() ?></th>
        </tr>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <td><?= $row->col1()->kt() ?></td>
                <td><?= $row->col2()->kt() ?></td>
                <td><?= $row->col3()->kt() ?></td>
                <td><?= $row->col4()->kt() ?></td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif; ?>