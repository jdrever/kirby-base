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
 * To use flat fields only, override defineFields():
 *
 *   protected function defineFields(): array
 *   {
 *       return [
 *           FormFieldSpec::textbox('location', 'Workshop name/location')->required(),
 *       ];
 *   }
 *
 * To group fields into sections (with optional conditional display), override
 * defineGroups() instead and return a mix of FormFieldSpec and FormSection objects:
 *
 *   protected function defineGroups(): array
 *   {
 *       return [
 *           FormFieldSpec::radioGroup('contact_preference', 'How do you prefer to be contacted?',
 *               ['Email', 'Phone']),
 *           FormSection::make('email_section', 'Email contact details')
 *               ->fields(FormFieldSpec::textbox('email', 'Email address'))
 *               ->showWhen('contact_preference', 'Email'),
 *           FormSection::make('phone_section', 'Phone contact details')
 *               ->fields(FormFieldSpec::textbox('phone', 'Phone number'))
 *               ->showWhen('contact_preference', 'Phone'),
 *       ];
 *   }
 */
abstract class BaseFormDefinition
{
    /**
     * Returns the ordered list of fixed FormFieldSpec objects for this form.
     * Override this for flat (no sections) forms.
     *
     * @return FormFieldSpec[]
     */
    protected function defineFields(): array
    {
        return [];
    }

    /**
     * Returns an ordered list of FormFieldSpec and/or FormSection objects.
     * Override this instead of defineFields() when you need to group fields
     * into sections with optional conditional display.
     *
     * Default implementation wraps defineFields() so existing subclasses that
     * only override defineFields() continue to work unchanged.
     *
     * @return array<FormFieldSpec|FormSection>
     */
    protected function defineGroups(): array
    {
        return $this->defineFields();
    }

    /**
     * Returns a short identifier for this form type (e.g. 'training_feedback').
     * Stored on every form_submission page for filtering and export.
     */
    abstract public function getFormType(): string;

    /**
     * Resolves all fixed fields against panel-supplied overrides from the given
     * Kirby page and returns an array of ready-to-render ResolvedFormField objects.
     *
     * Sections are flattened: fields inside sections are included in order.
     * For section-aware rendering use getFieldGroups() instead.
     *
     * @param Page $page The Kirby page holding the panel override field values
     * @return ResolvedFormField[]
     */
    public function getFields(Page $page): array
    {
        $resolved = [];

        foreach ($this->getAllSpecs() as $spec) {
            $resolved[] = $this->resolveSpec($spec, $page);
        }

        return $resolved;
    }

    /**
     * Resolves defineGroups() against panel-supplied overrides from the given
     * Kirby page and returns an ordered mixed array of ResolvedFormField and
     * ResolvedFormSection objects suitable for section-aware rendering.
     *
     * @param Page $page The Kirby page holding the panel override field values
     * @return array<ResolvedFormField|ResolvedFormSection>
     */
    public function getFieldGroups(Page $page): array
    {
        $groups = [];

        foreach ($this->defineGroups() as $item) {
            if ($item instanceof FormSection) {
                $groups[] = $item->resolve($page, fn(FormFieldSpec $s, Page $p) => $this->resolveSpec($s, $p));
            } else {
                $groups[] = $this->resolveSpec($item, $page);
            }
        }

        return $groups;
    }

    /**
     * Returns the field names of all fixed fields (including those inside
     * sections), in definition order.
     * Useful for identifying which POST keys belong to fixed vs custom fields.
     *
     * @return string[]
     */
    public function getFieldNames(): array
    {
        return array_map(
            static fn(FormFieldSpec $spec): string => $spec->getName(),
            $this->getAllSpecs()
        );
    }

    /**
     * Returns a merged Kirby-blueprint-compatible field definition array
     * covering all overridable properties across every field in this definition
     * (including fields inside sections).
     *
     * Developers can call this (e.g. via a CLI command or temporary debug route)
     * to generate the YAML to paste into a page blueprint.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toBlueprintFields(): array
    {
        $fields = [];

        foreach ($this->getAllSpecs() as $spec) {
            $fields = array_merge($fields, $spec->toBlueprintFields());
        }

        return $fields;
    }

    // ── Private helpers ─────────────────────────────────────────────────────

    /**
     * Returns a flat list of all FormFieldSpec objects from defineGroups(),
     * extracting specs from inside FormSection objects.
     *
     * @return FormFieldSpec[]
     */
    private function getAllSpecs(): array
    {
        $specs = [];

        foreach ($this->defineGroups() as $item) {
            if ($item instanceof FormSection) {
                foreach ($item->getFields() as $spec) {
                    $specs[] = $spec;
                }
            } else {
                $specs[] = $item;
            }
        }

        return $specs;
    }

    /**
     * Resolves a single FormFieldSpec against panel override values from the
     * given page and returns a ResolvedFormField.
     *
     * @param FormFieldSpec $spec
     * @param Page          $page
     * @return ResolvedFormField
     */
    private function resolveSpec(FormFieldSpec $spec, Page $page): ResolvedFormField
    {
        $panelValues = [];

        foreach (array_keys($spec->getOverridableProperties()) as $property) {
            $blueprintFieldName = $spec->getBlueprintFieldName($property);
            $panelValue = (string) $page->content()->get($blueprintFieldName)->value();

            if ($panelValue !== '') {
                $panelValues[$property] = $panelValue;
            }
        }

        return $spec->resolve($panelValues);
    }
}
