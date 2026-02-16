<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\RelatedContent;
use BSBI\WebBase\models\RelatedContentList;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the RelatedContent and RelatedContentList models.
 *
 * Covers RelatedContent constructor defaults, open-in-new-tab flag,
 * and RelatedContentList add/retrieve/empty-state behaviour.
 */
final class RelatedContentTest extends TestCase
{
    /**
     * Verify the constructor sets title, URL and defaults openInNewTab to false.
     */
    public function testConstructorSetsProperties(): void
    {
        $content = new RelatedContent('Related Article', '/articles/related');

        $this->assertSame('Related Article', $content->getTitle());
        $this->assertSame('/articles/related', $content->getUrl());
        $this->assertFalse($content->openInNewTab());
    }

    /**
     * Verify openInNewTab can be enabled via the constructor.
     */
    public function testOpenInNewTabCanBeEnabled(): void
    {
        $content = new RelatedContent('External', 'https://example.com', true);

        $this->assertTrue($content->openInNewTab());
    }

    // --- RelatedContentList ---

    /**
     * Verify related content items can be added and retrieved in order.
     */
    public function testAddAndRetrieveItems(): void
    {
        $list = new RelatedContentList();
        $item1 = new RelatedContent('Item 1', '/item-1');
        $item2 = new RelatedContent('Item 2', '/item-2');

        $list->addListItem($item1);
        $list->addListItem($item2);

        $this->assertSame(2, $list->count());
        $this->assertSame($item1, $list->getListItems()[0]);
    }

    /**
     * Verify a new RelatedContentList is empty by default.
     */
    public function testEmptyListByDefault(): void
    {
        $list = new RelatedContentList();

        $this->assertSame(0, $list->count());
        $this->assertFalse($list->hasListItems());
    }
}
