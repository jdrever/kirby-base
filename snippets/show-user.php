<?php
/**
 * Show user/logout link - static HTML, hidden by default.
 * JS will show this element if user is logged in (via user-status/script).
 */

declare(strict_types=1);

?>
<a href="<?= url('logout') ?>" <?php if (isset($class)) : ?> class="<?= $class ?>"<?php endif ?> data-user-logged-in style="display:none;">
    <?= t('Logout', 'Logout') ?>
</a>