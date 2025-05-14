<?php

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

if ($block->summary()->isNotEmpty()) : ?>
<details class="accordion">
    <summary class="h5"><?= $block->summary() ?></summary>
    <?= $block->details()->kt() ?>
</details>
<?php endif; ?>