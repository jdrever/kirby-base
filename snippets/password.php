<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

if (!isset($currentPage)) :
    throw new Exception('$currentPage was not provided');
endif;

?>
<style>
    .login-form {
        max-width: 330px;
        padding: 1rem;
    }
</style>

<form method="post" class="login-form">
    <fieldset class="border border-success rounded p-3">
        <ol class="list-unstyled">
            <li class="mb-3">
                <label for="password" class="form-label">
                    Password:
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       required="required"
                       aria-required="true"
                       class="form-control">
                    <input class="form-check-input" type="checkbox" id="showPasswordToggle">
                    <label class="form-check-label" for="showPasswordToggle">
                        Show Password
                    </label>
            </li>
            <li class="submit-buttons">
                <input type="submit" value="SEND" name="loginButton" class="btn btn-success">
            </li>
        </ol>
    </fieldset>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const showPasswordToggle = document.getElementById('showPasswordToggle');

        showPasswordToggle.addEventListener('change', function() {
            // Toggle the type attribute between 'password' and 'text'
            if (showPasswordToggle.checked) {
                passwordInput.setAttribute('type', 'text');
            } else {
                passwordInput.setAttribute('type', 'password');
            }
        });
    });
</script>