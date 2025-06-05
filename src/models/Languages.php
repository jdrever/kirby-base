<?php

namespace BSBI\WebBase\models;


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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): Languages
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function setCurrentLanguage(string $currentLanguage): Languages
    {
        $this->currentLanguage = $currentLanguage;
        return $this;
    }

    public function isPageTranslatedInCurrentLanguage(): bool
    {
        return $this->isPageTranslatedInCurrentLanguage;
    }

    public function setIsPageTranslatedInCurrentLanguage(bool $isPageTranslatedInCurrentLanguage): Languages
    {
        $this->isPageTranslatedInCurrentLanguage = $isPageTranslatedInCurrentLanguage;
        return $this;
    }

    public function isUsingDefaultLanguage(): bool
    {
        return $this->usingDefaultLanguage;
    }

    public function setUsingDefaultLanguage(bool $usingDefaultLanguage): Languages
    {
        $this->usingDefaultLanguage = $usingDefaultLanguage;
        return $this;
    }



}
