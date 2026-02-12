<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUndefinedMethodInspection */
/**
 * Video block - renders both consent placeholder and actual content for cacheability.
 * JS hydrates to show/hide based on localStorage consent state.
 */

declare(strict_types=1);

use Kirby\Cms\Block;
use Kirby\Toolkit\Html;
use BSBI\WebBase\helpers\KirbyInternalHelper;

$helper = new KirbyInternalHelper();
$requiresConsent = $helper->requiresCookieConstent();

/**
 * @var Block $block
 */

// Pre-compute video content
$videoContent = '';
$isYoutube = str_contains($block->url()->value(), 'youtu');

if ($isYoutube) :
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
        $ytId = $ytUrlVars['v'] ?? '';
    }

    if (!is_string($ytId) || empty($ytId)) {
        throw new Exception('video snippet: $ytId is not set or is not a string');
    }
endif;

?>
<?php if ($requiresConsent) : ?>
<div data-requires-consent>
    <?php snippet('block-consent', ['contentType' => 'video', 'purpose' => 'It will display an externally hosted video']); ?>
    <div data-consent-content style="display:none;">
<?php endif ?>
<?php if ($isYoutube) : ?>
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
<?php endif ?>
<?php if ($requiresConsent) : ?>
    </div>
</div>
<?php endif;
