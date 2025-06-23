<?php

declare(strict_types=1);



use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/** @var BaseWebPage $currentPage */
$mainContentBlocks = $currentPage->getMainContent();


if ($mainContentBlocks->hasBlockOfType('heading')) : ?>
<div class="p-2">
    <button class="btn btn-outline-success toc-toggle d-md-none" type="button" data-bs-toggle="collapse"
        data-bs-target="#tocContents" aria-expanded="false" aria-controls="tocContents">
        On this page
        <?= svg('/assets/images/icons/chevron-expand.svg') ?>
    </button>
    <p class="d-none d-md-block h4">On this page</p>
    <div class="collapse toc-collapse" id="tocContents">
        <nav id="toc">
            <ul>
    <?php
    foreach ($mainContentBlocks->getBlocks() as $contentBlock) :
        if ($contentBlock->getBlockType() === 'heading'
            && (in_array($contentBlock->getBlockLevel(), ['h2','h3']))) :
            $margin = $contentBlock->getBlockLevel()== 'h3' ? 'ps-4 small' : '';
            ?>
                <li>
                    <a href="#<?=$contentBlock->getAnchor()?>" class="<?= $margin ?>">
                        <?= strip_tags($contentBlock->getBlockContent()) ?>
                    </a>
                </li>
        <?php endif;
    endforeach;
    ?>
            </ul>
        </nav>
    </div>
</div>
<?php endif ?>