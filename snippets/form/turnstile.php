<?php

if (!isset($siteKey)) :
    throw new Exception("Missing turnstile site key");
endif;
?>
<div class="cf-turnstile" data-sitekey="<?=$siteKey?>" data-callback="javascriptCallback"></div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>