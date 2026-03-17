<?php
/**
 * Show logout link when a user is currently logged in.
 *
 * @var \BSBI\WebBase\models\BaseWebPage $currentPage
 * @var string|null $class Optional CSS class for the link
 */

declare(strict_types=1);

if (!isset($currentPage)) :
    throw new Exception('show-user snippet: $currentPage not provided');
endif;

if ($currentPage->hasCurrentUser()) : ?>
<a href="<?= url('logout') ?>"<?php if (isset($class)) : ?> class="<?= $class ?>"<?php endif ?>>
    <?= t('Logout', 'Logout') ?>
</a>
<?php endif ?>