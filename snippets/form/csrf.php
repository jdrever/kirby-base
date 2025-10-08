<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

if (!method_exists($currentPage, 'getCSRFToken')) :
    throw new Exception('$currentPage does not have a getCSRFToken method');
endif;

?>
<input type="hidden" name="csrf" value="<?= $currentPage->getCSRFToken() ?>">