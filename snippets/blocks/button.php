<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

?>
<a href="<?= $block->link() ?>" class="btn btn-primary m-2">
    <?= $block->text() ?>
</a>