<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Kirby\Template\Slots;

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not provided');
endif;

/**
 * @var Slots $slots
 */

/** @var BaseWebPage $currentPage */


$query = $currentPage->getQuery();
?>

<form method="get" role="search">
    <p>Search for:
        <input type="search" aria-label="Search" name="q" class="form-control-sm ms-2 me-2" value="<?=$query ?>">
        <button class="btn btn-sm btn-success me-1" type="submit">Search</button></p>
</form>