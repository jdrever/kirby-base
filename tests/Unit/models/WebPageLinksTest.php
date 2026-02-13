<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;
use PHPUnit\Framework\TestCase;

final class WebPageLinksTest extends TestCase
{
    private function createLink(
        string $title = 'Link',
        string $url = '/link',
        string $pageId = 'page-1',
        string $pageType = 'default'
    ): WebPageLink {
        return new WebPageLink($title, $url, $pageId, $pageType);
    }

    public function testAddListItemOnlyAddsCompletedLinks(): void
    {
        $links = new WebPageLinks();

        $goodLink = $this->createLink('Good');
        $links->addListItem($goodLink);

        $badLink = $this->createLink('Bad');
        $badLink->setStatus(false);
        $links->addListItem($badLink);

        $this->assertSame(1, $links->count());
    }

    public function testGetLinkFindsMatchingPageType(): void
    {
        $links = new WebPageLinks();
        $links->addListItem($this->createLink('Home', '/', 'home', 'homepage'));
        $links->addListItem($this->createLink('About', '/about', 'about', 'about'));

        $found = $links->getLink('about');
        $this->assertSame('About', $found->getTitle());
        $this->assertTrue($found->getStatus());
    }

    public function testGetLinkReturnsFailedStatusWhenNotFound(): void
    {
        $links = new WebPageLinks();
        $found = $links->getLink('nonexistent');

        $this->assertFalse($found->getStatus());
        $this->assertSame('NOT_FOUND', $found->getPageType());
    }

    public function testHasLinksIncludedInMenus(): void
    {
        $links = new WebPageLinks();

        $included = $this->createLink('Visible');
        $links->addListItem($included);

        $this->assertTrue($links->hasLinksIncludedInMenus());

        // Now test with only excluded links
        $links2 = new WebPageLinks();
        $excluded = $this->createLink('Hidden');
        $excluded->setExcludeFromMenus(true);
        $links2->addListItem($excluded);

        $this->assertFalse($links2->hasLinksIncludedInMenus());
    }
}
