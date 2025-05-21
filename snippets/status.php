<?php


declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/** @var BaseWebPage $currentPage */

if ($currentPage->hasFriendlyMessages()) :
    $messageStatus = $currentPage->getStatus() ? 'success' : 'danger';?>
<div class="alert alert-<?=$messageStatus?> alert-dismissible fade show" role="alert" id="statusAlert">
    <h2>
        <?php foreach ($currentPage->getFriendlyMessages() as $message) : ?>
            <?=$message?> <br>
        <?php endforeach ?>
    </h2>
    <a href="/" class="btn btn-outline-primary">Return to home page</a>
</div>

    <?php if (!$currentPage->getStatus()) :
    if ($currentPage->getCurrentUserRole()==='admin') : ?>
    <div class="p-2">
        <h3>Error details</h3>
        <?php foreach ($currentPage->getErrorMessages() as $message) : ?>
        <p><?=$message?></p>
        <?php endforeach ?>
    </div>
    <?php endif;?>
</body></html>
    <?php die();
    endif;?>
<?php endif ?>