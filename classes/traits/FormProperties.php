<?php

namespace BSBI\WebBase\traits;



use BSBI\WebBase\models\FeedbackForm;

/**
 *
 */
trait FormProperties {

    use ErrorHandling;

    private string $csrfToken = '';

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
}

