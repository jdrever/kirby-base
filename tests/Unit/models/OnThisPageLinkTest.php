<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\OnThisPageLink;
use BSBI\WebBase\models\OnThisPageLinks;
use PHPUnit\Framework\TestCase;

final class OnThisPageLinkTest extends TestCase
{
    private function createLink(
        string $title = 'Section',
        string $anchor = 'section',
        string $level = 'h2',
        string $area = 'main',
    ): OnThisPageLink {
        return new OnThisPageLink($title, $anchor, $level, $area);
    }

    // --- OnThisPageLink ---

    public function testConstructorSetsProperties(): void
    {
        $link = $this->createLink('Introduction', 'intro', 'h2', 'main');

        $this->assertSame('Introduction', $link->getTitle());
        $this->assertSame('intro', $link->getAnchorLink());
        $this->assertSame('h2', $link->getLinkLevel());
        $this->assertSame('main', $link->getLinkArea());
    }

    public function testSettersUpdateProperties(): void
    {
        $link = $this->createLink();
        $link->setAnchorLink('new-anchor');
        $link->setLinkLevel('h3');
        $link->setLinkArea('lower');

        $this->assertSame('new-anchor', $link->getAnchorLink());
        $this->assertSame('h3', $link->getLinkLevel());
        $this->assertSame('lower', $link->getLinkArea());
    }

    // --- OnThisPageLinks list ---

    public function testAddListItemOnlyAddsCompletedLinks(): void
    {
        $list = new OnThisPageLinks();
        $good = $this->createLink('Good', 'good', 'h2', 'main');
        $bad = $this->createLink('Bad', 'bad', 'h2', 'main');
        $bad->recordError('broken');

        $list->addListItem($good);
        $list->addListItem($bad);

        $this->assertSame(1, $list->count());
    }

    public function testAddListItemAfterMainInsertsAfterLastMainItem(): void
    {
        $list = new OnThisPageLinks();
        $main1 = $this->createLink('Main 1', 'm1', 'h2', 'main');
        $main2 = $this->createLink('Main 2', 'm2', 'h2', 'main');
        $lower1 = $this->createLink('Lower 1', 'l1', 'h2', 'lower');

        $list->addListItem($main1);
        $list->addListItem($main2);
        $list->addListItem($lower1);

        $inserted = $this->createLink('After Main', 'am', 'h3', 'main');
        $list->addListItemAfterMain($inserted);

        $items = $list->getListItems();
        $this->assertSame(4, $list->count());
        $this->assertSame('After Main', $items[2]->getTitle());
        $this->assertSame('Lower 1', $items[3]->getTitle());
    }

    public function testAddListItemAfterLowerInsertsAfterLastLowerItem(): void
    {
        $list = new OnThisPageLinks();
        $main1 = $this->createLink('Main 1', 'm1', 'h2', 'main');
        $lower1 = $this->createLink('Lower 1', 'l1', 'h2', 'lower');

        $list->addListItem($main1);
        $list->addListItem($lower1);

        $inserted = $this->createLink('After Lower', 'al', 'h3', 'lower');
        $list->addListItemAfterLower($inserted);

        $items = $list->getListItems();
        $this->assertSame(3, $list->count());
        $this->assertSame('After Lower', $items[2]->getTitle());
    }

    public function testAddListItemAfterAppendsWhenAreaNotFound(): void
    {
        $list = new OnThisPageLinks();
        $main1 = $this->createLink('Main 1', 'm1', 'h2', 'main');
        $list->addListItem($main1);

        $inserted = $this->createLink('After Lower', 'al', 'h3', 'lower');
        $list->addListItemAfterLower($inserted);

        $items = $list->getListItems();
        $this->assertSame(2, $list->count());
        $this->assertSame('After Lower', $items[1]->getTitle());
    }
}
