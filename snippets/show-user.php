<?php /** @noinspection PhpUnhandledExceptionInspection */

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
        <a href="<?= url('logout') ?>" <?php if (isset($class)) : ?> class="<?= $class ?>"<?php endif ?>>
            <?= t('Logout', 'Logout') ?>
        </a>
<?php endif;
endif ?>