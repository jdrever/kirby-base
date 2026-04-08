<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\sections;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the translatedpages panel section definition.
 *
 * The section's computed closure depends on Kirby's runtime context
 * ($this->model(), $this->kirby()) and cannot be executed in isolation.
 * These tests verify that the section file returns a well-formed definition
 * array with the expected structure.
 */
final class TranslatedPagesSectionTest extends TestCase
{
    /**
     * @var array<string, mixed> The loaded section definition.
     */
    private array $section;

    protected function setUp(): void
    {
        $this->section = require __DIR__ . '/../../../sections/translatedpages.php';
    }

    /**
     * Verify the section definition is an array.
     */
    public function testSectionDefinitionIsArray(): void
    {
        $this->assertIsArray($this->section);
    }

    /**
     * Verify a 'computed' key exists in the section definition.
     */
    public function testSectionHasComputedKey(): void
    {
        $this->assertArrayHasKey('computed', $this->section);
    }

    /**
     * Verify 'computed' contains a 'translations' entry.
     */
    public function testComputedHasTranslationsKey(): void
    {
        $this->assertArrayHasKey('translations', $this->section['computed']);
    }

    /**
     * Verify the 'translations' computed entry is callable.
     */
    public function testTranslationsIsCallable(): void
    {
        $this->assertIsCallable($this->section['computed']['translations']);
    }

    /**
     * Verify there are no unexpected top-level keys in the section definition.
     */
    public function testSectionHasNoUnexpectedKeys(): void
    {
        $allowedKeys = ['props', 'computed'];
        foreach (array_keys($this->section) as $key) {
            $this->assertContains(
                $key,
                $allowedKeys,
                "Unexpected key '$key' found in section definition."
            );
        }
    }
}
