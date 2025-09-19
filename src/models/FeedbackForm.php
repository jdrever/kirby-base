<?php

namespace BSBI\WebBase\models;


use BSBI\WebBase\traits\FormProperties;

class FeedbackForm {

    use FormProperties;

    private string $nameValue = '';

    private string $emailValue = '';

    private string $feedbackValue = '';

    private string $nameAlert = '';

    private string $emailAlert = '';
    private string $feedbackAlert = '';



    public function getNameValue(): string
    {
        return $this->nameValue;
    }

    public function setNameValue(string $nameValue): self
    {
        $this->nameValue = $nameValue;
        return $this;
    }

    public function getEmailValue(): string
    {
        return $this->emailValue;
    }

    public function setEmailValue(string $emailValue): self
    {
        $this->emailValue = $emailValue;
        return $this;
    }

    public function getFeedbackValue(): string
    {
        return $this->feedbackValue;
    }

    public function setFeedbackValue(string $feedbackValue): self
    {
        $this->feedbackValue = $feedbackValue;
        return $this;
    }

    public function getNameAlert(): string
    {
        return $this->nameAlert;
    }

    public function setNameAlert(string $nameAlert): self
    {
        $this->nameAlert = $nameAlert;
        return $this;
    }

    public function getEmailAlert(): string
    {
        return $this->emailAlert;
    }

    public function setEmailAlert(string $emailAlert): self
    {
        $this->emailAlert = $emailAlert;
        return $this;
    }

    public function getFeedbackAlert(): string
    {
        return $this->feedbackAlert;
    }

    public function setFeedbackAlert(string $feedbackAlert): self
    {
        $this->feedbackAlert = $feedbackAlert;
        return $this;
    }
}

