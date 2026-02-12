<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;

/**
 * @var Block $block
 */

$questions = $block->questions()->toStructure();
if ($questions->isNotEmpty()) :
    if ($block->title()->isNotEmpty()) :
        $blockTitle = str_replace(' ', '', mb_strtolower($block->title()->value()));?>
    <a id="<?=$blockTitle?>"></a>
    <h2><?=$block->title()?></h2>
    <?php endif;
    foreach ($questions as $question) : ?>
    <details class="as-accordion">
        <summary class="h5"><?= $question->question() ?></summary>
        <?= $question->answer()->kt() ?>
    </details>
    <?php endforeach ?>
<?php endif ?>