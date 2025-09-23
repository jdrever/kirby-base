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


    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): Language
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivePage(): bool
    {
        return $this->isActivePage;
    }

    /**
     * @param bool $isActivePage
     * @return $this
     */
    public function setIsActivePage(bool $isActivePage): Language
    {
        $this->isActivePage = $isActivePage;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentPageUrl(): string
    {
        return $this->currentPageUrl;
    }

    /**
     * @param string $currentPageUrl
     * @return $this
     */
    public function setCurrentPageUrl(string $currentPageUrl): Language
    {
        $this->currentPageUrl = $currentPageUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Language
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault(bool $isDefault): Language
    {
        $this->isDefault = $isDefault;
        return $this;
    }




}
