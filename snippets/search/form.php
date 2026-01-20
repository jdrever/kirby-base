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

<form method="get" role="search" <?php if (isset($searchUrl)) : ?> action="<?=$searchUrl?>" <?php endif; ?>
    <div class="row align-items-end bg-light p-2 rounded mb-3">
        <div class="col col-lg-5">
        <label class="form-label" for="q">Search for:</label>
        <input type="search" aria-label="Search" name="q" class="form-control-sm ms-2 me-2" value="<?=$query ?>" required>
        </div>
        <div class="col col-lg-5">
        <?php if (method_exists($currentPage, 'hasContentTypeOptions') &&
         method_exists($currentPage, 'getSelectedContentType')
         && $currentPage->hasContentTypeOptions()) :
            snippet('form/select', [
              'label' => 'Looking in: ',
              'id' => 'contentTypes',
              'name' => 'contentTypes',
              'selectedValue' => $currentPage->getSelectedContentType(),
              'options' => $currentPage->getContentTypeOptions()
            ]);
        endif ?>
        </div>
        <div class="col col-lg-2">
            <button class="btn btn-sm btn-success w-100" type="submit">Search</button>
        </div>
    </div>
</form>