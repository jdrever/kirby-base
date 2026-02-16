<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Language;
use BSBI\WebBase\models\Languages;
use PHPUnit\Framework\TestCase;

final class LanguageTest extends TestCase
{
    private function createLanguage(string $code = 'en', string $name = 'English'): Language
    {
        $lang = new Language();
        $lang->setCode($code)->setName($name);
        return $lang;
    }

    // --- Language ---

    public function testCodeGetterSetter(): void
    {
        $lang = $this->createLanguage('fr', 'French');

        $this->assertSame('fr', $lang->getCode());
        $this->assertSame('French', $lang->getName());
    }

    public function testActivePageGetterSetter(): void
    {
        $lang = $this->createLanguage();

        $lang->setIsActivePage(true);
        $this->assertTrue($lang->isActivePage());

        $lang->setIsActivePage(false);
        $this->assertFalse($lang->isActivePage());
    }

    public function testCurrentPageUrlGetterSetter(): void
    {
        $lang = $this->createLanguage();
        $lang->setCurrentPageUrl('/fr/about');

        $this->assertSame('/fr/about', $lang->getCurrentPageUrl());
    }

    public function testIsDefaultGetterSetter(): void
    {
        $lang = $this->createLanguage();

        $lang->setIsDefault(true);
        $this->assertTrue($lang->isDefault());
    }

    // --- Languages ---

    public function testLanguagesDisabledByDefault(): void
    {
        $languages = new Languages();

        $this->assertFalse($languages->isEnabled());
    }

    public function testLanguagesEnabledGetterSetter(): void
    {
        $languages = new Languages();
        $languages->setEnabled(true);

        $this->assertTrue($languages->isEnabled());
    }

    public function testAddAndRetrieveLanguages(): void
    {
        $languages = new Languages();
        $en = $this->createLanguage('en', 'English');
        $fr = $this->createLanguage('fr', 'French');

        $languages->addLanguage($en);
        $languages->addLanguage($fr);

        $this->assertCount(2, $languages->getLanguages());
        $this->assertSame($en, $languages->getLanguages()[0]);
    }

    public function testCurrentLanguageAndTranslationStatus(): void
    {
        $languages = new Languages();
        $languages->setCurrentLanguage('fr');
        $languages->setUsingDefaultLanguage(false);
        $languages->setIsPageTranslatedInCurrentLanguage(true);

        $this->assertSame('fr', $languages->getCurrentLanguage());
        $this->assertFalse($languages->isUsingDefaultLanguage());
        $this->assertTrue($languages->isPageTranslatedInCurrentLanguage());
    }
}
