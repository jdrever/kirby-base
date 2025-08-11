<?php

declare(strict_types=1);


use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not specified');
endif;

/** @var BaseWebPage $currentPage */

if ($currentPage->hasRelatedLinks()) : ?>
    <div class="p-4 mt-4 bg-light">
        <div class="container">
           
            <h3>Relevant to this page</h3>
            <div class="grid">
          
<?php
    if ($currentPage->hasTagLinks()) :
        $tagLinks = $currentPage->getTagLinks();
        foreach ($tagLinks->getListItems() as $tagLinkSet) :
            $tagLinksInSet = $tagLinkSet->getLinks();
            if ($tagLinksInSet->count() > 0) : ?>
                <div class="well bg-white p-3 g-col-md-4">
                    <h4><?=$tagLinkSet->getTagType()?></h4>
                    <ul>
                <?php foreach ($tagLinksInSet->getListItems() as $tagLink) : ?>
                        <li><a href="<?= $tagLink->getUrl() ?>"><?= $tagLink->getTitle()?></a></li>
                <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>
        <?php endforeach;
    endif;

    if ($currentPage->hasRelatedContent()) :
        $relatedContent = $currentPage->getRelatedContentList(); ?>
        <div class="well bg-white p-3 g-col-md-4">
            <h4>Related Content</h4>
            <ul>
                <?php foreach ($relatedContent->getListItems() as $content) : ?>
                    <li><a href="<?= $content->getUrl() ?>"
                        <?php if ($content->openInNewTab()) : ?>
                            target="_blank" rel="noopener noreferrer"
                        <?php endif ?>
                    >
                        <?= $content->getTitle() ?>
                    </a></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
            </div><!-- grid -->
        </div><!-- container -->
    </div>
<?php endif ?>
