<?php

declare(strict_types=1);

/**
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Site $site
 * @var \BSBI\Course\CustomDefaultPage $page
 * @var \Kirby\Cms\Block $block
 */
?>
<div class="container m-2 p-4 bg-light">
<?php if ($file = $block->file()->toFile()) : ?>  
    <a href="<?= $file->url()?>" target="_blank"><?=$block->label() != "" ? $block->label() : $file->filename()?></a>
    <a class="btn btn-primary btn-sm" href="<?= $file->url()?>" target="_blank">
        VIEW
    </a>
<?php else : ?>
    <p>No file</p>
<?php endif?>
</div>
