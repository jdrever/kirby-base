<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

/**
 * An immutable value object representing a form field with all properties
 * fully resolved (defaults merged with any panel overrides).
 *
 * Passed from BaseFormDefinition to template snippets.
 */
readonly class ResolvedFormField
{
    /**
     * @param string        $type        One of: textbox, textarea, checkbox-group, radio-group, likert, select
     * @param string        $name        The HTML input name attribute and POST key
     * @param string        $label       The human-readable question label
     * @param bool          $required    Whether the field is required
     * @param string        $inputType   For textbox: the HTML input type (text, email, date, …)
     * @param string        $help        Optional help text shown beneath the field
     * @param string[]      $options     For checkbox-group, radio-group, select: the option values
     * @param string        $leftLabel   For likert: left-end label
     * @param string        $middleLabel For likert: centre label
     * @param string        $rightLabel  For likert: right-end label
     * @param int           $scaleMin    For likert: minimum scale value
     * @param int           $scaleMax    For likert: maximum scale value
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly string $label,
        public readonly bool $required = false,
        public readonly string $inputType = 'text',
        public readonly string $help = '',
        public readonly array $options = [],
        public readonly string $leftLabel = 'Strongly disagree',
        public readonly string $middleLabel = '',
        public readonly string $rightLabel = 'Strongly agree',
        public readonly int $scaleMin = 1,
        public readonly int $scaleMax = 5,
    ) {
    }

    /**
     * Returns the arguments array to pass directly to the form/textbox snippet.
     *
     * @return array<string, mixed>
     */
    public function toTextboxArgs(): array
    {
        return [
            'id'       => $this->name,
            'name'     => $this->name,
            'label'    => $this->label,
            'type'     => $this->inputType,
            'required' => $this->required,
        ];
    }

    /**
     * Returns the arguments array to pass directly to the form/textarea snippet.
     *
     * @return array<string, mixed>
     */
    public function toTextareaArgs(): array
    {
        return [
            'id'       => $this->name,
            'name'     => $this->name,
            'label'    => $this->label,
            'required' => $this->required,
        ];
    }

    /**
     * Returns the arguments array to pass directly to the form/select snippet.
     * Converts the flat options array to the value/display pair format the snippet expects.
     *
     * @return array<string, mixed>
     */
    public function toSelectArgs(): array
    {
        return [
            'id'      => $this->name,
            'name'    => $this->name,
            'label'   => $this->label,
            'options' => array_map(
                static fn(string $o): array => ['value' => $o, 'display' => $o],
                $this->options
            ),
        ];
    }

    /**
     * Returns the arguments array to pass directly to the form/likert snippet.
     *
     * @return array<string, mixed>
     */
    public function toLikertArgs(): array
    {
        return [
            'name'        => $this->name,
            'label'       => $this->label,
            'leftLabel'   => $this->leftLabel,
            'middleLabel' => $this->middleLabel,
            'rightLabel'  => $this->rightLabel,
            'scaleMin'    => $this->scaleMin,
            'scaleMax'    => $this->scaleMax,
        ];
    }
}
