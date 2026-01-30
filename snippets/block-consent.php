<?php

declare(strict_types=1);

if (!isset($contentType)) :
  $contentType = 'content';
endif;
?>

<div class="alert alert-light">
  <p>This <?= $contentType ?> requires your consent</p>
  <?php if (isset($purpose)) : ?>
    <p><?= $purpose ?></p>
  <?php endif ?>
  <form action="/cookie-consent" method="post" class="mt-2">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <input type="hidden" name="referringPage" value="<?= kirby()->request()->url() ?>">
    <button type="submit" name="consent" value="accepted" class="btn btn-primary btn-sm">Accept cookies</button>
  </form>
</div>

