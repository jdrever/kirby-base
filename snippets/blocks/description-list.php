<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

$rows = $block->rows()->toStructure();
if ($rows->isNotEmpty()) :
    ?>
    <dl>
        <?php foreach ($rows as $row) : ?>
            <dt><?= $row->term() ?></dt>
            <dd><?= $row->description()->kt() ?></dd>
        <?php endforeach ?>
    </dl>
<?php endif ?>