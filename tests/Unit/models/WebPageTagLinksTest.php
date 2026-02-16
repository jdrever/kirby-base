<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\WebPageLinks;
use BSBI\WebBase\models\WebPageTagLinkSet;
use BSBI\WebBase\models\WebPageTagLinks;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the WebPageTagLinkSet and WebPageTagLinks models.
 *
 * Covers WebPageTagLinkSet tag type and links getters/setters,
 * and WebPageTagLinks list add/retrieve/empty-state behaviour.
 */
final class WebPageTagLinksTest extends TestCase
{
    /**
     * Create a WebPageTagLinkSet with sensible defaults for testing.
     *
     * @param string $title   The tag set title
     * @param string $tagType The tag type identifier
     * @return WebPageTagLinkSet
     */
    private function createTagLinkSet(string $title = 'Tag', string $tagType = 'category'): WebPageTagLinkSet
    {
        $set = new WebPageTagLinkSet($title);
        $set->setTagType($tagType);
        return $set;
    }

    // --- WebPageTagLinkSet ---

    /**
     * Verify tag type can be set and retrieved.
     */
    public function testTagTypeGetterSetter(): void
    {
        $set = $this->createTagLinkSet('Flowers', 'topic');

        $this->assertSame('topic', $set->getTagType());
    }

    /**
     * Verify hasLinks() returns false when no links have been assigned.
     */
    public function testHasLinksReturnsFalseWhenNotSet(): void
    {
        $set = $this->createTagLinkSet();

        $this->assertFalse($set->hasLinks());
    }

    /**
     * Verify links can be set and retrieved.
     */
    public function testSetAndGetLinks(): void
    {
        $set = $this->createTagLinkSet();
        $links = new WebPageLinks();
        $set->setLinks($links);

        $this->assertSame($links, $set->getLinks());
    }

    // --- WebPageTagLinks list ---

    /**
     * Verify tag link sets can be added and retrieved in order.
     */
    public function testAddAndRetrieveTagLinkSets(): void
    {
        $list = new WebPageTagLinks();
        $set1 = $this->createTagLinkSet('Tag A', 'category');
        $set2 = $this->createTagLinkSet('Tag B', 'topic');

        $list->addListItem($set1);
        $list->addListItem($set2);

        $this->assertSame(2, $list->count());
        $this->assertSame($set1, $list->getListItems()[0]);
    }

    /**
     * Verify a new WebPageTagLinks list is empty by default.
     */
    public function testEmptyByDefault(): void
    {
        $list = new WebPageTagLinks();

        $this->assertFalse($list->hasListItems());
    }
}
