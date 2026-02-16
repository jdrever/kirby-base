<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Language;
use BSBI\WebBase\models\Languages;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Language and Languages models.
 *
 * Covers Language code/name/active-page/default getters and setters,
 * and Languages enable/disable, current language tracking, and
 * translation status.
 */
final class LanguageTest extends TestCase
{
    /**
     * Create a Language with sensible defaults for testing.
     *
     * @param string $code The language code (e.g. 'en', 'fr')
     * @param string $name The display name of the language
     * @return Language
     */
    private function createLanguage(string $code = 'en', string $name = 'English'): Language
    {
        $lang = new Language();
        $lang->setCode($code)->setName($name);
        return $lang;
    }

    // --- Language ---

    /**
     * Verify code and name are correctly set and retrieved.
     */
    public function testCodeGetterSetter(): void
    {
        $lang = $this->createLanguage('fr', 'French');

        $this->assertSame('fr', $lang->getCode());
        $this->assertSame('French', $lang->getName());
    }

    /**
     * Verify the active page flag can be toggled.
     */
    public function testActivePageGetterSetter(): void
    {
        $lang = $this->createLanguage();

        $lang->setIsActivePage(true);
        $this->assertTrue($lang->isActivePage());

        $lang->setIsActivePage(false);
        $this->assertFalse($lang->isActivePage());
    }

    /**
     * Verify current page URL can be set and retrieved.
     */
    public function testCurrentPageUrlGetterSetter(): void
    {
        $lang = $this->createLanguage();
        $lang->setCurrentPageUrl('/fr/about');

        $this->assertSame('/fr/about', $lang->getCurrentPageUrl());
    }

    /**
     * Verify the default language flag can be set.
     */
    public function testIsDefaultGetterSetter(): void
    {
        $lang = $this->createLanguage();

        $lang->setIsDefault(true);
        $this->assertTrue($lang->isDefault());
    }

    // --- Languages ---

    /**
     * Verify Languages is disabled by default.
     */
    public function testLanguagesDisabledByDefault(): void
    {
        $languages = new Languages();

        $this->assertFalse($languages->isEnabled());
    }

    /**
     * Verify Languages can be enabled.
     */
    public function testLanguagesEnabledGetterSetter(): void
    {
        $languages = new Languages();
        $languages->setEnabled(true);

        $this->assertTrue($languages->isEnabled());
    }

    /**
     * Verify multiple languages can be added and retrieved in order.
     */
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

    /**
     * Verify current language, default language flag, and page translation status.
     */
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
