<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;
use PHPUnit\Framework\TestCase;

/**
 * Tests the CoreWebPageProperties trait via WebPageLink (a concrete user of the trait).
 *
 * Covers pageId, pageType, description, and subPages getters/setters,
 * including empty-state checks and addSubPage behaviour.
 */
final class CoreWebPagePropertiesTest extends TestCase
{
    /**
     * Create a WebPageLink for testing CoreWebPageProperties methods.
     *
     * @return WebPageLink
     */
    private function createLink(): WebPageLink
    {
        return new WebPageLink('Test Page', '/test', 'page-1', 'default');
    }

    /**
     * Verify pageId can be retrieved from constructor and updated via setter.
     */
    public function testPageIdGetterSetter(): void
    {
        $link = $this->createLink();

        $this->assertSame('page-1', $link->getPageId());

        $link->setPageId('page-99');
        $this->assertSame('page-99', $link->getPageId());
    }

    /**
     * Verify pageType can be retrieved from constructor and updated via setter.
     */
    public function testPageTypeGetterSetter(): void
    {
        $link = $this->createLink();

        $this->assertSame('default', $link->getPageType());

        $link->setPageType('article');
        $this->assertSame('article', $link->getPageType());
    }

    /**
     * Verify description defaults to empty and can be set.
     */
    public function testDescriptionGetterSetter(): void
    {
        $link = $this->createLink();

        $this->assertSame('', $link->getDescription());

        $link->setDescription('A test page');
        $this->assertTrue($link->hasDescription());
        $this->assertSame('A test page', $link->getDescription());
    }

    /**
     * Verify subPages reports as empty when initialised with an empty WebPageLinks.
     */
    public function testSubPagesInitiallyEmpty(): void
    {
        $link = $this->createLink();
        $link->setSubPages(new WebPageLinks());

        $this->assertFalse($link->hasSubPages());
    }

    /**
     * Verify addSubPage() adds to the subPages collection and hasSubPages() reflects it.
     */
    public function testAddSubPageAndHasSubPages(): void
    {
        $link = $this->createLink();
        $link->setSubPages(new WebPageLinks());

        $subPage = new WebPageLink('Sub', '/sub', 'sub-1', 'default');
        $link->addSubPage($subPage);

        $this->assertTrue($link->hasSubPages());
        $this->assertSame(1, $link->getSubPages()->count());
    }
}
