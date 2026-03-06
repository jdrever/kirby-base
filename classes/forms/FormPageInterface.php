<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

use Kirby\Cms\Blocks;

/**
 * Interface for pages that use the definition-based form system.
 *
 * Implemented by consuming-application FormPage classes that use the
 * FormProperties trait, allowing generic kirby-base snippets to render
 * form fields without depending on application-specific model classes.
 */
interface FormPageInterface
{
    /**
     * Returns the resolved fixed form fields from a BaseFormDefinition.
     *
     * @return ResolvedFormField[]
     */
    public function getFormFields(): array;

    /**
     * Returns custom form element blocks added by panel editors, or null
     * if no custom elements have been configured.
     */
    public function getCustomFormBlocks(): ?Blocks;

    /**
     * Returns true if the form has been successfully submitted in the
     * current request (used to skip rendering the form on success).
     */
    public function isSubmissionSuccessful(): bool;
}
