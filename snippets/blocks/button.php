<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

?>
<p>
    <a href="<?= $block->link() ?>" class="btn btn-primary">
    <?= $block->text() ?>
    </a>
</p>