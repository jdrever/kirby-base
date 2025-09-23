<?php

namespace BSBI\WebBase\models;


use BSBI\WebBase\traits\FormProperties;

/**
 *
 */
class FeedbackForm {

    use FormProperties;

    private string $nameValue = '';

    private string $emailValue = '';

    private string $feedbackValue = '';

    private string $nameAlert = '';

    private string $emailAlert = '';
    private string $feedbackAlert = '';


    /**
     * @return string
     */
    public function getNameValue(): string
    {
        return $this->nameValue;
    }

    /**
     * @param string $nameValue
     * @return $this
     */
    public function setNameValue(string $nameValue): self
    {
        $this->nameValue = $nameValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailValue(): string
    {
        return $this->emailValue;
    }

    /**
     * @param string $emailValue
     * @return $this
     */
    public function setEmailValue(string $emailValue): self
    {
        $this->emailValue = $emailValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getFeedbackValue(): string
    {
        return $this->feedbackValue;
    }

    /**
     * @param string $feedbackValue
     * @return $this
     */
    public function setFeedbackValue(string $feedbackValue): self
    {
        $this->feedbackValue = $feedbackValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getNameAlert(): string
    {
        return $this->nameAlert;
    }

    /**
     * @param string $nameAlert
     * @return $this
     */
    public function setNameAlert(string $nameAlert): self
    {
        $this->nameAlert = $nameAlert;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAlert(): string
    {
        return $this->emailAlert;
    }

    /**
     * @param string $emailAlert
     * @return $this
     */
    public function setEmailAlert(string $emailAlert): self
    {
        $this->emailAlert = $emailAlert;
        return $this;
    }

    /**
     * @return string
     */
    public function getFeedbackAlert(): string
    {
        return $this->feedbackAlert;
    }

    /**
     * @param string $feedbackAlert
     * @return $this
     */
    public function setFeedbackAlert(string $feedbackAlert): self
    {
        $this->feedbackAlert = $feedbackAlert;
        return $this;
    }
}

