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
            <th><?= $block->col1Title()->kt() ?></th>
            <th><?= $block->col2Title()->kt() ?></th>
        </tr>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <td><?= $row->col1()->kt() ?></td>
                <td><?= $row->col2()->kt() ?></td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif; ?>