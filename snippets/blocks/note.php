<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

?>

<div class="note">
    <?= $block->noteContent()->kt() ?>
</div>