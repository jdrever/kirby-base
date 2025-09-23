<?php

namespace BSBI\WebBase\models;


/**
 *
 */
class Languages
{

    private bool $enabled = false;
    private string $currentLanguage;

    private bool $usingDefaultLanguage = true;
    private bool $isPageTranslatedInCurrentLanguage = false;

    /**
     * @var Language [] $languages
     */
    private array $languages = [];

    /**
     * @param Language $language
     */
    public function addLanguage(Language $language): void
    {
        $this->languages [] = $language;
    }

    /**
     * @return Language[]
     */
    public function getLanguages(): array {
        return $this->languages;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled): Languages
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * @param string $currentLanguage
     * @return $this
     */
    public function setCurrentLanguage(string $currentLanguage): Languages
    {
        $this->currentLanguage = $currentLanguage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPageTranslatedInCurrentLanguage(): bool
    {
        return $this->isPageTranslatedInCurrentLanguage;
    }

    /**
     * @param bool $isPageTranslatedInCurrentLanguage
     * @return $this
     */
    public function setIsPageTranslatedInCurrentLanguage(bool $isPageTranslatedInCurrentLanguage): Languages
    {
        $this->isPageTranslatedInCurrentLanguage = $isPageTranslatedInCurrentLanguage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUsingDefaultLanguage(): bool
    {
        return $this->usingDefaultLanguage;
    }

    /**
     * @param bool $usingDefaultLanguage
     * @return $this
     */
    public function setUsingDefaultLanguage(bool $usingDefaultLanguage): Languages
    {
        $this->usingDefaultLanguage = $usingDefaultLanguage;
        return $this;
    }



}
