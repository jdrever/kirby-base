<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\forms\ResolvedFormField;
use Kirby\Cms\Blocks;

/**
 *
 */
trait FormProperties {

    use ErrorHandling;

    private string $csrfToken = '';

    private bool $submissionSuccessful = false;

    /** @var ResolvedFormField[] Fixed fields resolved from a BaseFormDefinition */
    private array $formFields = [];

    /** @var Blocks|null Custom form element blocks added by panel editors */
    private ?Blocks $customFormBlocks = null;

    /**
     * @return string
     */
    public function getCSRFToken(): string
    {
        return $this->csrfToken;
    }

    /**
     * @param string $csrfToken
     * @return static
     */
    public function setCSRFToken(string $csrfToken): static
    {
        $this->csrfToken = $csrfToken;
        return $this;
    }

    private string $turnstileSiteKey;

    /**
     * @return string
     */
    public function getTurnstileSiteKey(): string
    {
        return $this->turnstileSiteKey;
    }

    /**
     * @param string $turnstileSiteKey
     * @return $this
     */
    public function setTurnstileSiteKey(string $turnstileSiteKey): static
    {
        $this->turnstileSiteKey = $turnstileSiteKey;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSubmissionSuccessful(): bool
    {
        return $this->submissionSuccessful;
    }

    /**
     * @param bool $submissionSuccessful
     * @return $this
     */
    public function setSubmissionSuccessful(bool $submissionSuccessful): static
    {
        $this->submissionSuccessful = $submissionSuccessful;
        return $this;
    }

    /**
     * Returns the resolved fixed form fields set by BaseFormDefinition::getFields().
     *
     * @return ResolvedFormField[]
     */
    public function getFormFields(): array
    {
        return $this->formFields;
    }

    /**
     * @param ResolvedFormField[] $formFields
     * @return static
     */
    public function setFormFields(array $formFields): static
    {
        $this->formFields = $formFields;
        return $this;
    }

    /**
     * Returns the custom form element blocks added by panel editors.
     *
     * @return Blocks|null
     */
    public function getCustomFormBlocks(): ?Blocks
    {
        return $this->customFormBlocks;
    }

    /**
     * @param Blocks $customFormBlocks
     * @return static
     */
    public function setCustomFormBlocks(Blocks $customFormBlocks): static
    {
        $this->customFormBlocks = $customFormBlocks;
        return $this;
    }
}

