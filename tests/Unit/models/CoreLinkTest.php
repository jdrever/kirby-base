<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\CoreLink;
use BSBI\WebBase\models\CoreLinks;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the CoreLink and CoreLinks models.
 *
 * Covers CoreLink constructor properties, and CoreLinks list
 * add/retrieve, type-based lookup, and not-found behaviour.
 */
final class CoreLinkTest extends TestCase
{
    /**
     * Create a CoreLink with sensible defaults for testing.
     *
     * @param string $title The link title
     * @param string $url   The link URL
     * @param string $type  The core link type identifier
     * @return CoreLink
     */
    private function createCoreLink(
        string $title = 'Home',
        string $url = '/home',
        string $type = 'home',
    ): CoreLink {
        return new CoreLink($title, $url, $type);
    }

    // --- CoreLink ---

    /**
     * Verify the constructor correctly assigns title, URL and type.
     */
    public function testConstructorSetsProperties(): void
    {
        $link = $this->createCoreLink('Search', '/search', 'search');

        $this->assertSame('Search', $link->getTitle());
        $this->assertSame('/search', $link->getUrl());
        $this->assertSame('search', $link->getType());
    }

    // --- CoreLinks list ---

    /**
     * Verify core links can be added and counted.
     */
    public function testAddAndRetrieveLinks(): void
    {
        $list = new CoreLinks();
        $home = $this->createCoreLink('Home', '/home', 'home');
        $search = $this->createCoreLink('Search', '/search', 'search');

        $list->addListItem($home);
        $list->addListItem($search);

        $this->assertSame(2, $list->count());
    }

    /**
     * Verify getPage() finds a core link by its type and returns a completed result.
     */
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

    /**
     * Verify getPage() returns a not-found result when the type does not exist.
     */
    public function testGetPageReturnsNotFoundForMissingType(): void
    {
        $list = new CoreLinks();
        $home = $this->createCoreLink();
        $list->addListItem($home);

        $notFound = $list->getPage('nonexistent');
        $this->assertFalse($notFound->didComplete());
    }

    /**
     * Verify getPage() returns a not-found result on an empty list.
     */
    public function testEmptyListReturnsNotFound(): void
    {
        $list = new CoreLinks();
        $notFound = $list->getPage('any');

        $this->assertFalse($notFound->didComplete());
    }
}
