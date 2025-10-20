<?php

declare(strict_types=1);

/**
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Site $site
 * @var \BSBI\Course\CustomDefaultPage $page
 * @var \Kirby\Cms\Block $block
 */
?>
<a href="<?= $block->link() ?>" class="btn btn-primary m-2">
    <?= $block->text() ?>
</a>