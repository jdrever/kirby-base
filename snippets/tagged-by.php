<?php

declare(strict_types=1);


use BSBI\WebBase\models\BaseWebPage;
if (!isset($currentPage)) :
    throw new Exception('$currentPage was not specified');
endif;

/** @var BaseWebPage $currentPage */

if ($currentPage->hasTaggedByLinks()) :
    $taggedByLinks = $currentPage->getTaggedByLinks();
    if ($taggedByLinks->hasListItems()) : ?>
<div class="p-4 bg-success-subtle">
    <h3 class="text-center">Relevant to this page</h3>
        <div class="panel">
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
    </div>
</div>
    <?php endif;
endif ?>
