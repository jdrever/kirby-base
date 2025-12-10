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
  <p>Please give consent using the Accept button at the top of the screen, where you can also find more about our
    privacy policy.</p>
</div>

