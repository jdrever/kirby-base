<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\testing;

use BSBI\WebBase\Testing\KirbyContentBuilder;
use BSBI\WebBase\Testing\KirbyTestEnvironment;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks;
use Kirby\Cms\Page;
use Kirby\Cms\Structure;
use PHPUnit\Framework\TestCase;

/**
 * Tests for KirbyContentBuilder.
 *
 * A minimal Kirby App is booted once via KirbyTestEnvironment so the fabricated
 * content objects resolve their fields.
 */
final class KirbyContentBuilderTest extends TestCase
{
    private static KirbyContentBuilder $builder;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        KirbyTestEnvironment::boot('kbtest-content-builder');
        self::$builder = new KirbyContentBuilder();
    }

    public function testPageReturnsPageWithContent(): void
    {
        $page = self::$builder->page(['title' => 'Hello', 'intro' => 'World']);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('Hello', $page->content()->get('title')->value());
        $this->assertSame('World', $page->content()->get('intro')->value());
    }

    public function testPageGeneratesUniqueSlugs(): void
    {
        $first  = self::$builder->page();
        $second = self::$builder->page();

        $this->assertNotSame($first->slug(), $second->slug());
    }

    public function testPageHonoursExplicitSlug(): void
    {
        $page = self::$builder->page(['title' => 'X'], 'my-slug');

        $this->assertSame('my-slug', $page->slug());
    }

    public function testStructureReturnsStructureWithRows(): void
    {
        $structure = self::$builder->structure([['name' => 'Alice'], ['name' => 'Bob']]);

        $this->assertInstanceOf(Structure::class, $structure);
        $this->assertCount(2, $structure);
        $this->assertSame('Alice', $structure->first()->content()->get('name')->value());
    }

    public function testBlocksReturnsBlocksCollection(): void
    {
        $blocks = self::$builder->blocks([
            ['type' => 'text', 'content' => ['text' => 'Hello']],
            ['type' => 'heading', 'content' => ['text' => 'Title', 'level' => 'h2']],
        ]);

        $this->assertInstanceOf(Blocks::class, $blocks);
        $this->assertCount(2, $blocks);
    }

    public function testBlockReturnsSingleBlock(): void
    {
        $block = self::$builder->block(['type' => 'text', 'content' => ['text' => 'Hi there']]);

        $this->assertInstanceOf(Block::class, $block);
        $this->assertSame('text', $block->type());
        $this->assertSame('Hi there', $block->content()->get('text')->value());
    }
}
