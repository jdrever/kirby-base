<?php
/**
 * Generic cookie consent banner.
 * All data attributes are pre-wired for cookie-consent.js.
 * JS hides this banner once a consent choice has been recorded.
 *
 * @var string|null $privacyPolicyUrl  Optional URL to a privacy policy page.
 * @var string|null $description       Optional override for the banner description text.
 */

declare(strict_types=1);
?>
<div class="alert alert-light mb-0" role="dialog" id="cookieDialog" aria-labelledby="cookieTitle" aria-describedby="cookieDescription" data-cookie-consent-banner>
  <fieldset>
    <legend id="cookieTitle">Cookie Consent</legend>
    <p id="cookieDescription"><?= isset($description) ? $description : 'This site uses cookies to improve your experience.' ?></p>
    <button type="button" class="btn btn-primary" data-consent-accept>Accept</button>
    <button type="button" class="btn btn-secondary" data-consent-reject>Reject</button>
    <?php if (isset($privacyPolicyUrl)) : ?>
    <a href="<?= $privacyPolicyUrl ?>" class="btn btn-outline-primary">Privacy policy</a>
    <?php endif ?>
  </fieldset>
</div>
