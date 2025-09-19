<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/**
 * @var BaseWebPage $currentPage
 */
$scripts = $currentPage->getScripts();
foreach ($scripts as $script) : ?>
    <script src="/assets/js/<?=$script?>.js"></script>
<?php endforeach ?>