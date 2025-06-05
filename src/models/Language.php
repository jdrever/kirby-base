<?php

namespace BSBI\WebBase\models;

/**
 * Class Person
 * Represents a person (e.g. member of staff or trustee)
 *
 * @package BSBI\Web
 */
class Language {
    private string $code;
    private string $name;
    private string $currentPageUrl;

    private bool $isActivePage;

    private bool $isDefault;


    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): Language
    {
        $this->code = $code;
        return $this;
    }

    public function isActivePage(): bool
    {
        return $this->isActivePage;
    }

    public function setIsActivePage(bool $isActivePage): Language
    {
        $this->isActivePage = $isActivePage;
        return $this;
    }

    public function getCurrentPageUrl(): string
    {
        return $this->currentPageUrl;
    }

    public function setCurrentPageUrl(string $currentPageUrl): Language
    {
        $this->currentPageUrl = $currentPageUrl;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Language
    {
        $this->name = $name;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): Language
    {
        $this->isDefault = $isDefault;
        return $this;
    }




}
