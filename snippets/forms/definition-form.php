<?php

declare(strict_types=1);

use BSBI\WebBase\forms\FormPageInterface;

/**
 * Generic layout for definition-based forms.
 *
 * Renders fixed fields from a BaseFormDefinition followed by any custom
 * form element blocks added by panel editors, then a submit button.
 *
 * Expected variable:
 *   $currentPage  FormPageInterface  The current form page model
 */

if (!isset($currentPage)) :
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('definition-form snippet: $currentPage not provided');
endif;

if (!$currentPage instanceof FormPageInterface) :
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('definition-form snippet: $currentPage must implement FormPageInterface');
endif;

if ($currentPage->isSubmissionSuccessful()) :
    return;
endif;

?>
<div class="container bg-light pt-4 mb-2">
    <form method="post">
        <?php snippet('form/csrf') ?>

        <?php snippet('form/definition-fields', ['formFields' => $currentPage->getFormFields()]) ?>

        <?php
        $customBlocks = $currentPage->getCustomFormBlocks();
        if ($customBlocks !== null && $customBlocks->isNotEmpty()) :
            foreach ($customBlocks as $block) :?>
                <div class="mb-3">
                    <?php snippet('blocks/' . $block->type(), ['block' => $block]) ?>
                </div>
                <hr>
            <?php endforeach;
        endif; ?>

        <div class="container text-end">
            <?php snippet('form/button', ['value' => 'Submit']) ?>
        </div>
    </form>
</div>
