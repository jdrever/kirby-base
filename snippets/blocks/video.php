<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

use Kirby\Cms\Block;
use Kirby\Toolkit\Html;
use BSBI\WebBase\helpers\KirbyInternalHelper;

$helper = new KirbyInternalHelper();

if ($helper->requiresCookieConstent() && !$helper->hasCookieConsent()) :
    snippet('block-consent', ['purpose' => 'It will display an externally hosted video']);
    return;
endif;

/**
 * @var Block $block
 */

?>
<?php if (str_contains($block->url()->value(), 'youtu')) : ?>
    <?php
    $ytUrl = str_replace("https://", "", $block->url()->value());

    if (!is_string($ytUrl) || empty($ytUrl)) {
        throw new Exception('video snippet: $ytUrl is not set or is not a string');
    }

    if (str_starts_with($ytUrl, "youtu.be/")) {
        $ytId = str_replace("youtu.be/", "", $ytUrl);
    } elseif (str_starts_with($ytUrl, "www.youtube.com/embed/")) {
        $ytId = substr(str_replace("www.youtube.com/embed/", "", $ytUrl), 0, 11);
    } else {
        $parsedYtUrl = parse_url($ytUrl, PHP_URL_QUERY);
        if (!is_string($parsedYtUrl)) {
            throw new Exception('video snippet: $parsedYtUrl is not a string');
        }
        parse_str($parsedYtUrl, $ytUrlVars);
        $ytId = $ytUrlVars['v'];
    }

    if (!is_string($ytId) || empty($ytId)) {
        throw new Exception('video snippet: $ytId is not set or is not a string');
    }
    ?>
    <lite-youtube videoid="<?= $ytId ?>" playlabel="<?= $block->caption()->value() ?>">
        <a href="<?=$block->url()->value()?>" class="lty-playbtn" title="Play Video">
            <span class="lyt-visually-hidden">Play Video: <?= $block->caption() ?></span>
        </a>
    </lite-youtube>
<?php elseif ($video = Html::video($block->url())) : ?>
    <?= $video ?>
<?php endif ?>
<?php if ($block->caption()->isNotEmpty()) : ?>
    <p style="font-size:0.7em;"><?= $block->caption() ?></p>
<?php endif;
