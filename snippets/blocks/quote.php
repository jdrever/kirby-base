<?php

declare(strict_types=1);

/**
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Site $site
 * @var \BSBI\Course\CustomDefaultPage $page
 * @var \Kirby\Cms\Block $block
 */
?>
<?php
$figureClass = "";
if ($block->backgroundColour()->isNotEmpty() and $block->backgroundColour()->value() !== "#") :
    $figureClass .= ' has-background';
    $figureStyle = 'background-color:' . $block->backgroundColour() . ';';
endif ?>
<figure 
<?php if (!empty($figureClass)) : ?>
    class="<?=$figureClass ?>" 
<?php endif ?>
<?php if (!empty($figureStyle)) : ?>
    style="<?=$figureStyle?>"
<?php endif ?>
>
    <blockquote class="blockquote">
        <strong><?= $block->text() ?></strong>
    </blockquote>
<?php if ($block->citation()->isNotEmpty()) : ?>
    <figcaption class="blockquote">&mdash; <?= $block->citation() ?></figcaption>
<?php endif ?>
</figure>