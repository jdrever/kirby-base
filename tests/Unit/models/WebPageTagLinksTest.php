<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\WebPageLinks;
use BSBI\WebBase\models\WebPageTagLinkSet;
use BSBI\WebBase\models\WebPageTagLinks;
use PHPUnit\Framework\TestCase;

final class WebPageTagLinksTest extends TestCase
{
    private function createTagLinkSet(string $title = 'Tag', string $tagType = 'category'): WebPageTagLinkSet
    {
        $set = new WebPageTagLinkSet($title);
        $set->setTagType($tagType);
        return $set;
    }

    // --- WebPageTagLinkSet ---

    public function testTagTypeGetterSetter(): void
    {
        $set = $this->createTagLinkSet('Flowers', 'topic');

        $this->assertSame('topic', $set->getTagType());
    }

    public function testHasLinksReturnsFalseWhenNotSet(): void
    {
        $set = $this->createTagLinkSet();

        $this->assertFalse($set->hasLinks());
    }

    public function testSetAndGetLinks(): void
    {
        $set = $this->createTagLinkSet();
        $links = new WebPageLinks();
        $set->setLinks($links);

        $this->assertSame($links, $set->getLinks());
    }

    // --- WebPageTagLinks list ---

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

    public function testEmptyByDefault(): void
    {
        $list = new WebPageTagLinks();

        $this->assertFalse($list->hasListItems());
    }
}
