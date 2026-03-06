<?php

declare(strict_types=1);

use BSBI\WebBase\forms\FormPageInterface;
use BSBI\WebBase\forms\ResolvedFormField;
use BSBI\WebBase\forms\ResolvedFormSection;

/**
 * Generic layout for definition-based forms.
 *
 * Renders fixed fields (and sections) from a BaseFormDefinition followed by
 * any custom form element blocks added by panel editors, then a submit button.
 *
 * Sections with a showWhen() condition are hidden by default and revealed
 * client-side when the controlling radio or select field matches the expected value.
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

$groups = $currentPage->getFormFieldGroups();
$hasConditionalSections = false;

foreach ($groups as $item) :
    if ($item instanceof ResolvedFormSection && $item->isConditional()) :
        $hasConditionalSections = true;
        break;
    endif;
endforeach;

?>
<div class="container bg-light pt-4 mb-2">
    <form method="post">
        <?php snippet('form/csrf') ?>

        <?php foreach ($groups as $item) : ?>
            <?php if ($item instanceof ResolvedFormSection) : ?>
                <?php snippet('form/section', ['section' => $item]) ?>
            <?php elseif ($item instanceof ResolvedFormField) : ?>
                <div class="mb-3">
                    <?php snippet('form/field-' . $item->type, ['field' => $item]) ?>
                </div>
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>

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

<?php if ($hasConditionalSections) : ?>
<script>
(function () {
    'use strict';

    var form = document.currentScript.previousElementSibling.querySelector('form');
    if (!form) { return; }

    function getSections() {
        return Array.from(form.querySelectorAll('fieldset[data-condition-field]'));
    }

    function updateSections() {
        getSections().forEach(function (section) {
            var conditionField = section.dataset.conditionField;
            var conditionValue = section.dataset.conditionValue;

            // Support radio groups (checked input) and selects
            var controlEl = form.querySelector('select[name="' + conditionField + '"]');
            var currentValue = '';

            if (controlEl) {
                currentValue = controlEl.value;
            } else {
                var checked = form.querySelector('input[name="' + conditionField + '"]:checked');
                if (checked) {
                    currentValue = checked.value;
                }
            }

            var shouldShow = (currentValue === conditionValue);
            section.style.display = shouldShow ? '' : 'none';

            // Disable inputs inside hidden sections so they are not submitted
            Array.from(section.querySelectorAll('input, select, textarea')).forEach(function (el) {
                el.disabled = !shouldShow;
            });
        });
    }

    // Run on any change within the form
    form.addEventListener('change', updateSections);

    // Initialise state on load
    updateSections();
}());
</script>
<?php endif; ?>
