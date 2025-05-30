<?php

declare(strict_types=1);

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not provided');
endif;

if (!method_exists($currentPage, 'getLoginDetails')) :
    throw new Exception('$currentPage must implement getLoginDetails method');
endif;

$loginDetails = $currentPage->getLoginDetails();


?>

<?php if ($loginDetails->hasBeenProcessed()) :?>
    <div class="alert alert-danger" role="alert">
        <h2><i class="bi bi-exclamation-square-fill"></i> <?= $loginDetails->getLoginMessage() ?></h2>
    </div>
<?php endif ?>

<form method="post">
    <input type="hidden" name="csrf" value="<?= $loginDetails->getCSRFToken() ?>">
    <input type="hidden" name="redirectPage" id="redirectPage" value="<?=$loginDetails->getRedirectPage()?>">
    <fieldset class="border border-success rounded p-3">
        <ol class="list-unstyled">
            <li class="mb-3">
                <label for="email" class="form-label">Username:
                </label>
                <input type="text" name="userName" required="required" aria-required="true" class="form-control"
                       value="<?= $loginDetails->getUserName() ?>">
            </li>
            <li class="mb-3">
                <label for="password" class="form-label">
                    Password:
                </label>
                <input type="password" name="password" required="required" aria-required="true" class="form-control">
            </li>
            <li class="submit-buttons">
                <input type="submit" value="LOGIN" name="loginButton" class="btn btn-success">
            </li>
        </ol>
    </fieldset>
</form>