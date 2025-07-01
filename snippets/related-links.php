<?php

declare(strict_types=1);


use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not specified');
endif;

/** @var BaseWebPage $currentPage */

if ($currentPage->hasRelatedLinks()) : ?>
    <div class="p-4 bg-success-subtle">
        <h3 class="text-center">Relevant to this page</h3>
        <div class="panel">
<?php
    $taggedByLinks = $currentPage->getTaggedByLinks();
    if ($taggedByLinks->hasListItems()) : ?>


        <?php foreach ($taggedByLinks->getListItems() as $taggedBySet) :
            if ($taggedBySet->hasLinks()) :
                $taggedBySetLinks =$taggedBySet->getLinks(); ?>
            <div>
                <h4><?=$taggedBySet->getTagType()?></h4>
                <ul>
                <?php foreach ($taggedBySetLinks->getListItems() as $taggedBy) : ?>
                    <li><a href="<?=$taggedBy->getUrl()?>"><?=$taggedBy->getTitle() ?></a></li>
                <?php endforeach ?>
                </ul>
            </div>
            <?php endif;
        endforeach ?>
    <?php endif;

    if ($currentPage->hasTags()) :
        $tagLinks = $currentPage->getTagLinks();
        foreach ($tagLinks->getListItems() as $tagLinkSet) :
            $tagLinksInSet = $tagLinkSet->getLinks();
            if ($tagLinksInSet->count() > 0) : ?>
                <div>
                    <h4>Related <?=$tagLinkSet->getTagType()?></h4>
                    <ul>
                <?php foreach ($tagLinksInSet->getListItems() as $tagLink) : ?>
                        <li><a href="<?= $tagLink->getUrl() ?>"><?= $tagLink->getTitle()?></a>
                <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>
        <?php endforeach;
    endif;

    if ($currentPage->hasRelatedContent()) :
        $relatedContent = $currentPage->getRelatedContentList(); ?>
        <div>
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
        </div>
    </div>
<?php endif ?>
