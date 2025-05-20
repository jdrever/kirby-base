<?php

declare(strict_types=1);

use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/**
 * @var BaseWebPage $currentPage
 */

if ($currentPage->hasCurrentUser()) :
    $currentUser = $currentPage->getCurrentUser();
    $userRole = $currentUser->getRole();
    if ($currentUser->isLoggedIn()) : ?>
        <a class="btn btn-primary mt-2 mb-1" href="<?= url('logout') ?>">
            <span style="font-size:0.7em;"><?= $currentUser->getUserName() ?></span>

            <?= svg('/assets/icons/door-closed-fill.svg') ?><?= t('Logout', 'Logout') ?>
        </a>
<?php endif;
endif ?>