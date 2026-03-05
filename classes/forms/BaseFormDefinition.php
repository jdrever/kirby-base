<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

use Kirby\Cms\Page;

/**
 * Abstract base class for form definitions.
 *
 * Subclasses declare the fixed form fields (including which properties are
 * overridable by panel editors) and provide a form-type identifier used when
 * storing submissions.
 *
 * Usage — create one subclass per form type in the consuming application:
 *
 *   class TrainingFormDefinition extends BaseFormDefinition
 *   {
 *       public function getFormType(): string { return 'training_feedback'; }
 *
 *       protected function defineFields(): array
 *       {
 *           return [
 *               FormFieldSpec::textbox('location', 'Workshop name/location')->required(),
 *               FormFieldSpec::likert('knowledge_start', 'Rate your knowledge')
 *                   ->overridable('label')
 *                   ->overridable('leftLabel', 'No knowledge at all'),
 *           ];
 *       }
 *   }
 */
abstract class BaseFormDefinition
{
    /**
     * Returns the ordered list of fixed FormFieldSpec objects for this form.
     *
     * @return FormFieldSpec[]
     */
    abstract protected function defineFields(): array;

    /**
     * Returns a short identifier for this form type (e.g. 'training_feedback').
     * Stored on every form_submission page for filtering and export.
     */
    abstract public function getFormType(): string;

    /**
     * Resolves all fixed fields against panel-supplied overrides from the given
     * Kirby page and returns an array of ready-to-render ResolvedFormField objects.
     *
     * For each overridable property on each field, the corresponding panel field
     * (named via FormFieldSpec::getBlueprintFieldName()) is read from the page.
     * If the panel field is non-empty it overrides the hard-coded default.
     *
     * @param Page $page The Kirby page holding the panel override field values
     * @return ResolvedFormField[]
     */
    public function getFields(Page $page): array
    {
        $resolved = [];

        foreach ($this->defineFields() as $spec) {
            $panelValues = [];

            foreach (array_keys($spec->getOverridableProperties()) as $property) {
                $blueprintFieldName = $spec->getBlueprintFieldName($property);
                $panelValue = (string) $page->content()->get($blueprintFieldName)->value();

                if ($panelValue !== '') {
                    $panelValues[$property] = $panelValue;
                }
            }

            $resolved[] = $spec->resolve($panelValues);
        }

        return $resolved;
    }

    /**
     * Returns the field names of all fixed fields, in definition order.
     * Useful for identifying which POST keys belong to fixed vs custom fields.
     *
     * @return string[]
     */
    public function getFieldNames(): array
    {
        return array_map(
            static fn(FormFieldSpec $spec): string => $spec->getName(),
            $this->defineFields()
        );
    }

    /**
     * Returns a merged Kirby-blueprint-compatible field definition array
     * covering all overridable properties across every field in this definition.
     *
     * Developers can call this (e.g. via a CLI command or temporary debug route)
     * to generate the YAML to paste into a page blueprint.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toBlueprintFields(): array
    {
        $fields = [];

        foreach ($this->defineFields() as $spec) {
            $fields = array_merge($fields, $spec->toBlueprintFields());
        }

        return $fields;
    }
}
