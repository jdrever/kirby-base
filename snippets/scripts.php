<?php

declare(strict_types=1);

use BSBI\Web\models\WebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/**
 * @var WebPage $currentPage
 */
$scripts = $currentPage->getScripts();
foreach ($scripts as $script) : ?>
    <script src="/assets/js/<?=$script?>.js"></script>
<?php endforeach ?>