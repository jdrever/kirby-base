<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

$figureClass = "well p-3";
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
    <blockquote class="blockquote  mt-2">
        <p><?= $block->text() ?></p>
    </blockquote>
<?php if ($block->citation()->isNotEmpty()) : ?>
    <figcaption class="blockquote-footer"><?= $block->citation() ?></figcaption>
<?php endif ?>
</figure>