<?php
/**
 * Consent placeholder for blocked content - static HTML, hydrated by JS.
 * This is the placeholder shown when consent has not been given.
 * Use with data-consent-placeholder attribute within a data-requires-consent container.
 */

declare(strict_types=1);

if (!isset($contentType)) :
  $contentType = 'content';
endif;
?>

<div class="alert alert-light" data-consent-placeholder>
  <p>This <?= $contentType ?> requires your consent</p>
  <?php if (isset($purpose)) : ?>
    <p><?= $purpose ?></p>
  <?php endif ?>
  <div class="mt-2">
    <button type="button" class="btn btn-primary btn-sm" data-consent-accept>Accept cookies</button>
    <button type="button" class="btn btn-secondary btn-sm" data-consent-reject data-consent-reject-btn>Reject</button>
  </div>
</div>

