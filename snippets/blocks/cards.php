<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;
use Kirby\Cms\StructureObject;

/**
 * @var Block $block
 */

$cards = $block->cards()->toStructure();
if ($cards->isEmpty()) {
    return;
}

$columns = $block->columns()->or('3')->value();
$colClass = match ($columns) {
    '2' => 'col-12 col-md-6',
    '4' => 'col-12 col-sm-6 col-lg-3',
    default => 'col-12 col-sm-6 col-lg-4',
};

?>
<?php if ($block->title()->isNotEmpty()): ?>
    <h2 class="text-center mb-4"><?= $block->title() ?></h2>
<?php endif ?>
<div class="row align-items-stretch justify-content-center">
    <?php foreach ($cards as $card): ?>
        <?php
        /** @var StructureObject $card */
        $url = $card->url()->isNotEmpty() ? $card->url()->value() : null;
        $image = $card->image()->isNotEmpty() ? $card->image()->toFile() : null;
        ?>
        <div class="<?= $colClass ?> mb-4 d-flex">
            <?= $url ? '<a href="' . esc($url) . '" class="card border-0 flex-fill text-decoration-none">' : '<div class="card border-0 flex-fill">' ?>
                <?php if ($image): ?>
                    <img src="<?= $image->url() ?>" class="card-img-top" alt="<?= $image->alt()->esc() ?>">
                <?php endif ?>
                <div class="card-body p-4">
                    <?php if ($card->title()->isNotEmpty()): ?>
                        <h3 class="card-title"><?= $card->title() ?></h3>
                    <?php endif ?>
                    <?php if ($card->text()->isNotEmpty()): ?>
                        <?= $card->text()->kt() ?>
                    <?php endif ?>
                </div>
            <?= $url ? '</a>' : '</div>' ?>
        </div>
    <?php endforeach ?>
</div>
