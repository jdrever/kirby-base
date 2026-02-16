<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\CoreLink;
use BSBI\WebBase\models\CoreLinks;
use PHPUnit\Framework\TestCase;

final class CoreLinkTest extends TestCase
{
    private function createCoreLink(
        string $title = 'Home',
        string $url = '/home',
        string $type = 'home',
    ): CoreLink {
        return new CoreLink($title, $url, $type);
    }

    // --- CoreLink ---

    public function testConstructorSetsProperties(): void
    {
        $link = $this->createCoreLink('Search', '/search', 'search');

        $this->assertSame('Search', $link->getTitle());
        $this->assertSame('/search', $link->getUrl());
        $this->assertSame('search', $link->getType());
    }

    // --- CoreLinks list ---

    public function testAddAndRetrieveLinks(): void
    {
        $list = new CoreLinks();
        $home = $this->createCoreLink('Home', '/home', 'home');
        $search = $this->createCoreLink('Search', '/search', 'search');

        $list->addListItem($home);
        $list->addListItem($search);

        $this->assertSame(2, $list->count());
    }

    public function testGetPageFindsLinkByType(): void
    {
        $list = new CoreLinks();
        $home = $this->createCoreLink('Home', '/home', 'home');
        $search = $this->createCoreLink('Search', '/search', 'search');

        $list->addListItem($home);
        $list->addListItem($search);

        $found = $list->getPage('search');
        $this->assertSame('Search', $found->getTitle());
        $this->assertTrue($found->didComplete());
    }

    public function testGetPageReturnsNotFoundForMissingType(): void
    {
        $list = new CoreLinks();
        $home = $this->createCoreLink();
        $list->addListItem($home);

        $notFound = $list->getPage('nonexistent');
        $this->assertFalse($notFound->didComplete());
    }

    public function testEmptyListReturnsNotFound(): void
    {
        $list = new CoreLinks();
        $notFound = $list->getPage('any');

        $this->assertFalse($notFound->didComplete());
    }
}
