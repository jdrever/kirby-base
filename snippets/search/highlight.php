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
<div class="container border p-1 mb-2 bg-warning-subtle"><p><strong>Highlighting search term: <span class="highlight"><?=$query?></strong></p></div>
<?php endif ?>