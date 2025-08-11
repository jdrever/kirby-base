<?php

declare(strict_types=1);

use BSBI\Docs\models\WebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/**
 * @var WebPage $currentPage
 */
if ($currentPage->getPageType()!=='search' && $currentPage->hasQuery()) :
    $query = $currentPage->getQuery(); ?>
<div class="p-3 bg-warning-subtle"><p><strong><img src="/assets/images/icons/search.svg" alt="" style="width:35px; height: 35px;">Highlighting search term: <span class="highlight"><?=$query?></strong></p></div>
<?php endif ?>