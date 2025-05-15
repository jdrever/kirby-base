<?php

declare(strict_types=1);

/**
 * @var App $kirby
 * @var Site $site
 * @var Block $block
 */

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Site;

?>
<h2><?=$block->title()?></h2>

<?php if ($block->links()->isNotEmpty()) : ?>
<ul>
    <?php foreach ($block->links()->toPages() as $link) : ?>
    <li><a href="<?=$link->url()?>"><?=$link->title()?></a></li>
    <?php endforeach?>
</ul>
<?php endif ?>
