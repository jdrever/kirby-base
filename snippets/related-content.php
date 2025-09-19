<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('related-content snippet: $currentPage not provided');
endif;

/** @var BaseWebPage $currentPage **/


if ($currentPage->hasRelatedContent()) :
    $relatedContent = $currentPage->getRelatedContentList(); ?>
    <h4>Related Content</h4>
    <div class="list-group">
        <?php foreach ($relatedContent->getListItems() as $content) : ?>
            <a href="<?= $content->getUrl() ?>" class="list-group-item list-group-item-action"
                <?php if ($content->openInNewTab()) : ?>
                    target="_blank" rel="noopener noreferrer"
                <?php endif ?>
            >
                <?= $content->getTitle() ?>
            </a>
        <?php endforeach ?>
    </div>
<?php endif ?>