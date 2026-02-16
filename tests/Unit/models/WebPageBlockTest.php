<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\WebPageBlock;
use PHPUnit\Framework\TestCase;

final class WebPageBlockTest extends TestCase
{
    private function createBlock(string $type = 'text', string $content = '<p>Hello</p>'): WebPageBlock
    {
        return new WebPageBlock($type, $content);
    }

    // --- Constructor ---

    public function testConstructorSetsTypeAndContent(): void
    {
        $block = $this->createBlock('heading', '<h2>Title</h2>');

        $this->assertSame('heading', $block->getBlockType());
        $this->assertSame('<h2>Title</h2>', $block->getBlockContent());
    }

    // --- Block level ---

    public function testBlockLevelGetterSetter(): void
    {
        $block = $this->createBlock();
        $block->setBlockLevel('h2');

        $this->assertSame('h2', $block->getBlockLevel());
    }

    // --- Anchor ---

    public function testAnchorGetterSetter(): void
    {
        $block = $this->createBlock();
        $block->setAnchor('section-one');

        $this->assertSame('section-one', $block->getAnchor());
    }

    // --- Fluent setters ---

    public function testSettersReturnSelf(): void
    {
        $block = $this->createBlock();

        $this->assertSame($block, $block->setBlockType('image'));
        $this->assertSame($block, $block->setBlockContent('<img>'));
        $this->assertSame($block, $block->setBlockLevel('h3'));
        $this->assertSame($block, $block->setAnchor('anchor'));
    }
}
