<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Image;
use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;
use PHPUnit\Framework\TestCase;

final class WebPageLinkTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $link = new WebPageLink('About Us', '/about', 'about', 'about_page');

        $this->assertSame('About Us', $link->getTitle());
        $this->assertSame('/about', $link->getUrl());
        $this->assertSame('about', $link->getPageId());
        $this->assertSame('about_page', $link->getPageType());
        $this->assertTrue($link->getStatus());
    }

    public function testGetFormattedPageType(): void
    {
        $link = new WebPageLink('', '', '', 'file_archive');
        $this->assertSame('File archive', $link->getFormattedPageType());
    }

    public function testImageHandling(): void
    {
        $link = new WebPageLink('', '', '', '');

        $this->assertFalse($link->hasImage());

        $image = new Image('photo.jpg');
        $link->setImage($image);

        $this->assertTrue($link->hasImage());
        $this->assertSame('photo.jpg', $link->getImage()->getSrc());
    }

    public function testMenuExclusion(): void
    {
        $link = new WebPageLink('', '', '', '');

        $this->assertTrue($link->doIncludeInMenus());
        $this->assertFalse($link->doExcludeFromMenus());

        $link->setExcludeFromMenus(true);

        $this->assertFalse($link->doIncludeInMenus());
        $this->assertTrue($link->doExcludeFromMenus());
    }

    public function testSubPages(): void
    {
        $link = new WebPageLink('Parent', '/parent', 'parent', 'default');

        $this->assertFalse($link->hasSubPages());

        $subPages = new WebPageLinks();
        $subPages->addListItem(new WebPageLink('Child', '/child', 'child', 'default'));
        $link->setSubPages($subPages);

        $this->assertTrue($link->hasSubPages());
        $this->assertSame(1, $link->getSubPages()->count());
    }

    public function testDescription(): void
    {
        $link = new WebPageLink('', '', '', '');
        $link->setDescription('A page about things');

        $this->assertTrue($link->hasDescription());
        $this->assertSame('A page about things', $link->getDescription());
    }
}
