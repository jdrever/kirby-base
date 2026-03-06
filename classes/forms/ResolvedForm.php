<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

use Kirby\Cms\Blocks;

/**
 * An immutable value object carrying all data needed to render a definition-based
 * form in the form/definition-form snippet.
 *
 * Built from a FormPage model via FormProperties::getResolvedForm() and passed
 * directly to the snippet, decoupling snippet rendering from the page model.
 */
readonly class ResolvedForm
{
    /**
     * @param array<ResolvedFormField|ResolvedFormSection> $fieldGroups         Fixed form fields/sections from a BaseFormDefinition
     * @param Blocks|null                                  $customBlocks        Custom form element blocks added by panel editors
     * @param bool                                         $submissionSuccessful Whether the form was successfully submitted this request
     * @param string                                       $csrfToken           CSRF token to render as a hidden input
     */
    public function __construct(
        public readonly array $fieldGroups,
        public readonly ?Blocks $customBlocks,
        public readonly bool $submissionSuccessful,
        public readonly string $csrfToken,
    ) {
    }
}
