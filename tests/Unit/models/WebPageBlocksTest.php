<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\WebPageBlock;
use BSBI\WebBase\models\WebPageBlocks;
use PHPUnit\Framework\TestCase;

final class WebPageBlocksTest extends TestCase
{
    public function testAddAndRetrieveBlocks(): void
    {
        $blocks = new WebPageBlocks();
        $blocks->addListItem(new WebPageBlock('heading', '<h2>Title</h2>'));
        $blocks->addListItem(new WebPageBlock('text', '<p>Content</p>'));

        $this->assertSame(2, $blocks->count());
        $this->assertCount(2, $blocks->getListItems());
    }

    public function testHasBlockOfType(): void
    {
        $blocks = new WebPageBlocks();
        $blocks->addListItem(new WebPageBlock('heading', '<h2>Title</h2>'));
        $blocks->addListItem(new WebPageBlock('table_simple', '<table></table>'));

        $this->assertTrue($blocks->hasBlockOfType('heading'));
        $this->assertFalse($blocks->hasBlockOfType('gallery'));
    }

    public function testHasBlockTypeStarting(): void
    {
        $blocks = new WebPageBlocks();
        $blocks->addListItem(new WebPageBlock('table_simple', '<table></table>'));
        $blocks->addListItem(new WebPageBlock('table_advanced', '<table></table>'));

        $this->assertTrue($blocks->hasBlockTypeStarting('table'));
        $this->assertFalse($blocks->hasBlockTypeStarting('image'));
    }

    public function testGetAllContentAsHTML(): void
    {
        $blocks = new WebPageBlocks();
        $blocks->addListItem(new WebPageBlock('heading', '<h2>Title</h2>'));
        $blocks->addListItem(new WebPageBlock('text', '<p>Body</p>'));

        $this->assertSame('<h2>Title</h2><p>Body</p>', $blocks->getAllContentAsHTML());
    }

    public function testGetAllContentAsHTMLWhenEmpty(): void
    {
        $blocks = new WebPageBlocks();
        $this->assertSame('', $blocks->getAllContentAsHTML());
    }
}
