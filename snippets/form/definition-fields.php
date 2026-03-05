<?php

declare(strict_types=1);

use BSBI\WebBase\forms\FormFieldSpec;
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
    endif;

    echo '<div class="mb-3">';

    match ($field->type) {
        FormFieldSpec::TYPE_TEXTBOX => snippet('form/textbox', $field->toTextboxArgs()),

        FormFieldSpec::TYPE_TEXTAREA => snippet('form/textarea', $field->toTextareaArgs()),

        FormFieldSpec::TYPE_LIKERT => snippet('form/likert', $field->toLikertArgs()),

        FormFieldSpec::TYPE_CHECKBOX_GROUP => (static function () use ($field): void {
            echo '<p><strong>' . html($field->label) . '</strong></p>';
            foreach ($field->options as $index => $option) :
                snippet('form/checkbox', [
                    'label'           => $option,
                    'id'              => $field->name . '_' . $index,
                    'name'            => $field->name . '[]',
                    'checkboxOrRadio' => 'checkbox',
                    'value'           => $option,
                    'labelLayout'     => 'small',
                ]);
            endforeach;
        })(),

        FormFieldSpec::TYPE_RADIO_GROUP => (static function () use ($field): void {
            echo '<p><strong>' . html($field->label) . '</strong></p>';
            foreach ($field->options as $index => $option) :
                snippet('form/checkbox', [
                    'label'           => $option,
                    'id'              => $field->name . '_' . $index,
                    'name'            => $field->name,
                    'checkboxOrRadio' => 'radio',
                    'value'           => $option,
                    'labelLayout'     => 'small',
                ]);
            endforeach;
        })(),

        FormFieldSpec::TYPE_SELECT => (static function () use ($field): void {
            $options = array_map(
                static fn(string $o): array => ['value' => $o, 'display' => $o],
                $field->options
            );
            snippet('form/select', [
                'id'      => $field->name,
                'name'    => $field->name,
                'label'   => $field->label,
                'options' => $options,
            ]);
        })(),

        default => null,
    };

    echo '</div>';
    echo '<hr>';

endforeach;
