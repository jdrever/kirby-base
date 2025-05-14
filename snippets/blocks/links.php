<?php

declare(strict_types=1);

/**
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Block $block
 */
?>
<h2><?=$block->title()?></h2>

<?php if ($block->links()->isNotEmpty()) : ?>
<ul>
    <?php foreach ($block->links()->toPages() as $link) : ?>
    <li><a href="<?=$link->url()?>"><?=$link->title()?></a></li>
    <?php endforeach?>
</ul>
<?php endif ?>
