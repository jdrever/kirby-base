<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\WebPageBlock;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the WebPageBlock model.
 *
 * Covers constructor initialisation, block type/level/content/anchor
 * getters and setters, and fluent interface return values.
 */
final class WebPageBlockTest extends TestCase
{
    /**
     * Create a WebPageBlock with sensible defaults for testing.
     *
     * @param string $type    The block type identifier
     * @param string $content The HTML content of the block
     * @return WebPageBlock
     */
    private function createBlock(string $type = 'text', string $content = '<p>Hello</p>'): WebPageBlock
    {
        return new WebPageBlock($type, $content);
    }

    // --- Constructor ---

    /**
     * Verify the constructor correctly assigns the block type and content.
     */
    public function testConstructorSetsTypeAndContent(): void
    {
        $block = $this->createBlock('heading', '<h2>Title</h2>');

        $this->assertSame('heading', $block->getBlockType());
        $this->assertSame('<h2>Title</h2>', $block->getBlockContent());
    }

    // --- Block level ---

    /**
     * Verify block level can be set and retrieved.
     */
    public function testBlockLevelGetterSetter(): void
    {
        $block = $this->createBlock();
        $block->setBlockLevel('h2');

        $this->assertSame('h2', $block->getBlockLevel());
    }

    // --- Anchor ---

    /**
     * Verify anchor can be set and retrieved.
     */
    public function testAnchorGetterSetter(): void
    {
        $block = $this->createBlock();
        $block->setAnchor('section-one');

        $this->assertSame('section-one', $block->getAnchor());
    }

    // --- Fluent setters ---

    /**
     * Verify all setters return the same instance for fluent chaining.
     */
    public function testSettersReturnSelf(): void
    {
        $block = $this->createBlock();

        $this->assertSame($block, $block->setBlockType('image'));
        $this->assertSame($block, $block->setBlockContent('<img>'));
        $this->assertSame($block, $block->setBlockLevel('h3'));
        $this->assertSame($block, $block->setAnchor('anchor'));
    }
}
