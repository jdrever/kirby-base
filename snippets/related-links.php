<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not specified');
endif;

/** @var BaseWebPage $currentPage */

if ($currentPage->hasRelatedLinks()) : ?>
    <div class="p-4 mt-4 bg-light d-print-none">
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
                        <?php
                        $visibleItems = $tagLinksInSet->getFirstXItems(5);
                        $hiddenItems = $tagLinksInSet->getItemsFromIndex(6);

                        foreach ($visibleItems as $tagLink) : ?>
                            <li><a href="<?= $tagLink->getUrl() ?>"><?= $tagLink->getTitle()?></a></li>
                        <?php endforeach ?>
                    </ul>
                        <?php if (count($hiddenItems) > 0) : ?>
                    <details>
                        <summary>See More</summary>
                        <ul>
                            <?php foreach ($hiddenItems as $tagLink) : ?>
                                <li><a href="<?= $tagLink->getUrl() ?>"><?= $tagLink->getTitle()?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                        <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endforeach;
                endif; ?>
            </div><!-- grid -->
            <?php if ($currentPage->hasRelatedContent()) :
                $relatedContent = $currentPage->getRelatedContentList(); ?>
                <div class="row">
                    <?php foreach($relatedContent->getListItems() as $link): ?>
                        <div class="col-12 col-lg-4 mb-3">
                            <a href="<?=$link->getUrl()?>" class="card border-0 h-100">
                                <?php if($link->hasImage()):
                                    snippet('image',[
                                        'image' => $link->getImage(),
                                        'class' => 'card-img-top img-fix-size img-fix-size--four-three'
                                    ]) ?>
                                <?php endif ?>
                                <div class="card-body p-4">
                                    <h3 class="card-title"><?=$link->getTitle()?></h3>
                                    <p class="card-text"><?=$link->getLinkDescription()?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div><!-- container -->
    </div>
<?php endif ?>
