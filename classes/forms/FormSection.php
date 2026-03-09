<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

use Kirby\Cms\Page;

/**
 * Declares a named section grouping one or more form fields, with an optional
 * condition that controls whether the section is displayed.
 *
 * Usage:
 *
 *   FormSection::make('contact_details', 'Contact Details')
 *       ->fields(
 *           FormFieldSpec::textbox('email', 'Email address'),
 *           FormFieldSpec::textbox('phone', 'Phone number'),
 *       )
 *       ->showWhen('contact_preference', 'email_or_phone');
 *
 * Call BaseFormDefinition::defineGroups() (instead of defineFields()) to mix
 * bare FormFieldSpec objects and FormSection objects in a single definition.
 */
class FormSection
{
    /** @var FormFieldSpec[] */
    private array $fields = [];

    private ?string $conditionField = null;

    private ?string $conditionValue = null;

    /**
     * @param string $id    Unique identifier for this section (used as HTML id prefix)
     * @param string $title Optional visible title rendered as a <legend>
     */
    private function __construct(
        private readonly string $id,
        private readonly string $title,
    ) {
    }

    // ── Static factory ──────────────────────────────────────────────────────

    /**
     * Creates a new FormSection builder.
     *
     * @param string $id    Unique identifier for this section
     * @param string $title Optional visible title (rendered as <legend>)
     */
    public static function make(string $id, string $title = ''): static
    {
        return new static($id, $title);
    }

    // ── Fluent modifiers ────────────────────────────────────────────────────

    /**
     * Adds one or more FormFieldSpec objects to this section.
     *
     * @param FormFieldSpec ...$fields
     */
    public function fields(FormFieldSpec ...$fields): static
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     * Sets a condition: this section is only displayed when the named field
     * has the given value.
     *
     * For the first iteration only radio-group and select fields are supported
     * as condition triggers.
     *
     * @param string $fieldName  The HTML name of the controlling field
     * @param string $value      The value that must be selected for this section to show
     */
    public function showWhen(string $fieldName, string $value): static
    {
        $this->conditionField = $fieldName;
        $this->conditionValue = $value;
        return $this;
    }

    // ── Accessors ───────────────────────────────────────────────────────────

    /** @return string The section identifier */
    public function getId(): string
    {
        return $this->id;
    }

    /** @return string The optional section title */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns all FormFieldSpec objects declared in this section.
     *
     * @return FormFieldSpec[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Returns the name of the field that controls section visibility, or null
     * if this section is unconditional.
     */
    public function getConditionField(): ?string
    {
        return $this->conditionField;
    }

    /**
     * Returns the value the controlling field must have for this section to be
     * displayed, or null if this section is unconditional.
     */
    public function getConditionValue(): ?string
    {
        return $this->conditionValue;
    }

    // ── Resolution ──────────────────────────────────────────────────────────

    /**
     * Resolves all fields in this section against panel overrides on the given
     * page and returns an immutable ResolvedFormSection.
     *
     * @param Page     $page        The Kirby page holding panel override values
     * @param callable $resolveSpec Callable(FormFieldSpec, Page): ResolvedFormField
     * @return ResolvedFormSection
     */
    public function resolve(Page $page, callable $resolveSpec): ResolvedFormSection
    {
        $resolvedFields = array_map(
            static fn(FormFieldSpec $spec): ResolvedFormField => $resolveSpec($spec, $page),
            $this->fields
        );

        return new ResolvedFormSection(
            id:             $this->id,
            title:          $this->title,
            fields:         $resolvedFields,
            conditionField: $this->conditionField,
            conditionValue: $this->conditionValue,
        );
    }
}
