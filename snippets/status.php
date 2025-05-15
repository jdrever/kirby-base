<?php


declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/** @var BaseWebPage $currentPage */

if ($currentPage->hasFriendlyMessages()) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert" id="statusAlert">
    <h2>
        <?php foreach ($currentPage->getFriendlyMessages() as $message) : ?>
            <?=$message?> <br>
        <?php endforeach ?>
    </h2>
</div>
<?php endif ?>