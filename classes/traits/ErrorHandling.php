<?php

namespace BSBI\WebBase\traits;

/**
 *
 */
trait ErrorHandling {

    /** @var string[] The error message(s) related to the model */
    protected array $errorMessages = [];

    /** @var string[] The friendly message(s) related to the model */
    protected array $friendlyMessages = [];

    /** @var bool The status related to the model */
    protected bool $status;

    protected bool $isCriticalError;

    /**
     * Get the errorMessage(s)
     * @return string []
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @return string
     */
    public function getFirstErrorMessage(): string {
        return $this->errorMessages[0] ?? '';
    }



    /**
     * @return bool
     */
    public function hasErrors(): bool {
        return (!$this->status);
    }

    /**
     * Set the value of errorMessage
     * @param string $errorMessage
     * @return ErrorHandling
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
     * @return bool
     */
    public function hasFriendlyMessages(): bool {
        return (count($this->friendlyMessages)>0);
    }


    /**
     * get the friendly error message(s)
     * @return string[]
     */
    public function getFriendlyMessages(): array
    {
        return $this->friendlyMessages;
    }

    /**
     * @param string $friendlyMessage
     * @return $this
     */
    public function addFriendlyMessage(string $friendlyMessage): static
    {
        $this->friendlyMessages[] = $friendlyMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstFriendlyMessage(): string {
        return $this->friendlyMessages[0] ?? '';
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

    /**
     * @param string $errorMessage
     * @param string $friendlyMessage
     * @return $this
     */
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
     * @return bool
     */
    public function isCriticalError(): bool
    {
        return $this->isCriticalError ?? true;
    }

    /**
     * @param bool $isCriticalError
     * @return $this
     */
    public function setIsCriticalError(bool $isCriticalError): static
    {
        $this->isCriticalError = $isCriticalError;
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