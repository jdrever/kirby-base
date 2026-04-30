<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

?>
<p>
    <a href="<?= $block->link()->toUrl() ?>"
       class="btn btn-primary"
       <?= $block->openInNewTab()->isTrue() ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
    <?= $block->text() ?>
    </a>
</p>