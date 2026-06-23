<?php

declare(strict_types=1);

namespace BSBI\WebBase\Testing;

use Kirby\Cms\Block;
use Kirby\Cms\Blocks;
use Kirby\Cms\Page;
use Kirby\Cms\Structure;
use Kirby\Data\Json;
use Kirby\Data\Yaml;

/**
 * Fabricates in-memory Kirby content objects (pages, structures, blocks) for
 * tests, with no filesystem content needed.
 *
 * PHPUnit-agnostic on purpose (see KirbyTestEnvironment): test frameworks
 * compose this builder; they do not extend it. A Kirby App must be booted first
 * (use {@see KirbyTestEnvironment::boot()}) so the produced objects resolve
 * their fields correctly.
 */
final class KirbyContentBuilder
{
    /**
     * Builds an in-memory page with the given content fields.
     *
     * @param array<string, mixed> $content Content field values keyed by name.
     * @param string|null $slug Optional slug; a unique one is generated if null.
     * @return Page The fabricated page.
     */
    public function page(array $content = [], ?string $slug = null): Page
    {
        return Page::factory([
            'slug'    => $slug ?? 'test-' . uniqid(),
            'content' => $content,
        ]);
    }

    /**
     * Builds a structure field collection from an array of rows.
     *
     * @param array<int, array<string, mixed>> $rows Structure rows.
     * @param string $field The field name to store the structure under.
     * @return Structure The fabricated structure collection.
     */
    public function structure(array $rows, string $field = 'items'): Structure
    {
        $page = $this->page([$field => Yaml::encode($rows)]);

        return $page->content()->get($field)->toStructure();
    }

    /**
     * Builds a blocks collection from an array of block definitions.
     *
     * @param array<int, array<string, mixed>> $blocks Block definitions.
     * @param string $field The field name to store the blocks under.
     * @return Blocks The fabricated blocks collection.
     */
    public function blocks(array $blocks, string $field = 'content'): Blocks
    {
        $page = $this->page([$field => Json::encode($blocks)]);

        return $page->content()->get($field)->toBlocks();
    }

    /**
     * Builds a single block. Convenience wrapper around {@see self::blocks()}.
     *
     * @param array<string, mixed> $block A single block definition.
     * @param string $field The field name to store the block under.
     * @return Block The fabricated block.
     */
    public function block(array $block, string $field = 'content'): Block
    {
        return $this->blocks([$block], $field)->first();
    }
}
