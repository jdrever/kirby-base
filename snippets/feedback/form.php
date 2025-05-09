<?php

declare(strict_types=1);

use BSBI\Web\models\FeedbackPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

if (!$currentPage instanceOf FeedbackPage) :
    throw new Exception('$currentPage not instance of FeedbackPage');
endif;

$feedbackForm = $currentPage->getFeedbackForm();

?>

<form method="post">
  <div class="mb-3">
      <?php snippet('form/textbox', [
          'id' => 'name',
          'name' => 'name',
          'label' => 'Your name:',
          'value' => $feedbackForm->getNameValue(),
          'alert' => $feedbackForm->getNameAlert(),
          'required' => true]) ?>
  </div>
  <div class="mb-3">
      <?php snippet('form/textbox', [
          'id' => 'email',
          'name' => 'email',
          'label' => 'Your email:',
          'value' => $feedbackForm->getEmailValue(),
          'alert' => $feedbackForm->getEmailAlert(),
          'required' => true]) ?>
  </div>
  <div class="mb-3">
      <?php snippet('form/textarea', [
          'id' => 'feedback',
          'name' => 'feedback',
          'label' => 'Your feedback:',
          'value' => $feedbackForm->getFeedbackValue(),
          'alert' => $feedbackForm->getFeedbackAlert(),
          'required' => true]) ?>
  <?php snippet('form/turnstile', ['siteKey' => $feedbackForm->getTurnstileSiteKey()]) ?>
    <input type="hidden" id="page" name="page" value="<?=get('page')?>">
    <input type="submit" name="submit" value="Submit" class="btn btn-outline-success">
</form>