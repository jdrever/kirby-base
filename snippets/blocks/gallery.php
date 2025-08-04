<?php

declare(strict_types=1);

/**
 * @var App $kirby
 * @var Site $site
 * @var CustomDefaultPage $page
 * @var Block $block
 */

use BSBI\Course\CustomDefaultPage;
use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Site;

$overallCaption = $block->caption();
$crop    = $block->crop()->isTrue();
$ratio   = $block->ratio()->or('auto');
$fullWidth = $block->fullWidth()->toBool();
snippet('base/full-width-block-starts', ['fullWidth' => $fullWidth]) ?>
<div class="container">
    <div class="row row-cols-2">
<?php foreach ($block->images()->toFiles() as $image) : ?>
        <div class="col">
<?php
    $alt     = $image->alt();
    $caption = $image->caption();
    //$crop    = $image->crop()->isTrue();
    $link    = $image->link();
    //$ratio   = $image->ratio()->or('auto');
    $src     = null;

    if ($block->fixedWidth()->isNotEmpty())
    {
        $image=$image->resize((int)$block->fixedWidth()->value());
    }
    $src = $image->url();

    $imgClass="figure-img img-fluid";
    $figCaptionClass="figure-caption";
?>
<?php if ($src): ?>
<figure<?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
  <?php if ($link->isNotEmpty()): ?>
  <a href="<?= Str::esc($link->toUrl()) ?>">
    <img class="<?=$imgClass?>" src="<?= $src ?>" alt="<?= $alt->esc() ?>">
  </a>
  <?php else: ?>
  <img class="<?=$imgClass?>" src="<?= $src ?>" alt="<?= $alt->esc() ?>">
  <?php endif ?>

  <?php if ($caption->isNotEmpty()): ?>
  <figcaption class="<?=$figCaptionClass?>">
    <?= $caption->kt() ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
        </div>
<?php endforeach ?>
    </div>
</div>
<?php if ($overallCaption->isNotEmpty()): ?>
<p class="<?=$figCaptionClass?>">
    <?= $overallCaption ?>
</p>
<?php endif;

snippet('base/full-width-block-ends', ['fullWidth' => $fullWidth]);
