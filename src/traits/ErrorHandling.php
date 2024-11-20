<?php

namespace BSBI\WebBase\traits;

trait ErrorHandling {

    /** @var string[] The error message(s) related to the model */
    protected array $errorMessages = [];

    /** @var string[] The friendly message(s) related to the model */
    protected array $friendlyMessages = [];

    /** @var bool The status related to the model */
    protected bool $status;

    /**
     * Get the errorMessage(s)
     * @return string []
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }


    /**
     * Returns true if has status of false
     * @return bool
     */
    public function hasErrors(): bool {
        return (!$this->status);
    }

    /**
     * Set the value of errorMessage
     */
    public function addErrorMessage(string $errorMessage): static
    {
        $this->errorMessages [] = $errorMessage;

        return $this;
    }


    /**
     * Add errorMessages
     * @param string[] $errorMessages
     */
    public function addErrorMessages(array $errorMessages): static
    {
        $this->errorMessages = array_merge($this->errorMessages, $errorMessages);
        return $this;
    }


    /**
     * get the friendly error message(s)
     * @return string[]
     */
    public function getFriendlyMessages(): array
    {
        return $this->friendlyMessages;
    }

    public function addFriendlyMessage(string $friendlyMessage): static
    {
        $this->friendlyMessages[] = $friendlyMessage;

        return $this;
    }

    /**
     * Add errorMessages
     * @param string[] $friendlyMessages
     */
    public function addFriendlyMessages(array $friendlyMessages): static
    {
        $this->friendlyMessages = array_merge($this->friendlyMessages, $friendlyMessages);
        return $this;
    }

    public function recordError(string $errorMessage, string $friendlyMessage =''): static {
        $this->addErrorMessage($errorMessage);
        if (!empty($friendlyMessage)) {
            $this->addFriendlyMessage($friendlyMessage);
        }
        $this->setStatus(false);
        return $this;
    }


    /**
     * @param string[] $errorMessages
     * @param string[] $friendlyMessages
     * @return $this
     */
    public function recordErrors(array $errorMessages, array $friendlyMessages): static {
        $this->addErrorMessages($errorMessages);
        $this->addFriendlyMessages($friendlyMessages);
        $this->setStatus(false);
        return $this;
    }

    /**
     * did the model complete (e.g. was status true)?
     * @return bool
     */
    public function didComplete() : bool
    {
        return $this->status===true;
    }

    /**
     * did the model not complete (e.g. was status false)?
     * @return bool
     */
    public function didNotComplete() : bool
    {
        return $this->status===false;
    }

    /**
     * Get the value of status
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * Set the value of status
     */
    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

}