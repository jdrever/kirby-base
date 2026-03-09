<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/**
 * Renders a list of ResolvedFormField objects produced by BaseFormDefinition::getFields().
 *
 * Expected variable:
 *   $formFields  ResolvedFormField[]   The resolved fixed fields to render
 */

if (!isset($formFields) || !is_array($formFields)) :
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('definition-fields snippet: $formFields not provided');
endif;

foreach ($formFields as $field) :
    if (!$field instanceof ResolvedFormField) :
        continue;
    endif; ?>
    <div class="mb-3">
        <?php snippet('form/field-' . $field->type, ['field' => $field]) ?>
    </div>
    <hr>
<?php endforeach;
