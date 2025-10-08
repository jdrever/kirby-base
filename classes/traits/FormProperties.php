<?php

namespace BSBI\WebBase\traits;



use BSBI\WebBase\models\FeedbackForm;

/**
 *
 */
trait FormProperties {

    use ErrorHandling;

    private string $csrfToken = '';

    private bool $submissionSuccessful = false;

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
     * @return FeedbackForm|FormProperties
     */
    public function setTurnstileSiteKey(string $turnstileSiteKey): self
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
}

