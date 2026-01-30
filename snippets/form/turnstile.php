<?php /** @noinspection PhpUnhandledExceptionInspection */

if (!isset($siteKey)) :
    throw new Exception("Missing turnstile site key");
endif;
?>
<div class="cf-turnstile" data-sitekey="<?=$siteKey?>"></div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>