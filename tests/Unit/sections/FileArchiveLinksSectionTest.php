<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\sections;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the filearchivelinks panel section definition.
 *
 * The 'links' computed closure depends on Kirby's runtime context
 * ($this->model(), the FileLinkIndexHelper database) and cannot be executed in
 * isolation. These tests verify the section returns a well-formed definition
 * and that the configurable 'headline' / 'emptyText' prop closures resolve
 * their defaults and overrides correctly — the prop closures are pure and need
 * no Kirby bootstrap.
 */
final class FileArchiveLinksSectionTest extends TestCase
{
    /**
     * @var array<string, mixed> The loaded section definition.
     */
    private array $section;

    protected function setUp(): void
    {
        $this->section = require __DIR__ . '/../../../sections/filearchivelinks.php';
    }

    /**
     * Verify the section definition is an array with the expected top-level keys.
     */
    public function testSectionDefinitionShape(): void
    {
        $this->assertIsArray($this->section);
        $this->assertArrayHasKey('props', $this->section);
        $this->assertArrayHasKey('computed', $this->section);
    }

    /**
     * Verify the configurable props are present and callable.
     */
    public function testConfigurablePropsAreCallable(): void
    {
        $this->assertIsCallable($this->section['props']['headline']);
        $this->assertIsCallable($this->section['props']['emptyText']);
        $this->assertIsCallable($this->section['props']['indexReady']);
    }

    /**
     * Verify the 'links' computed entry is callable.
     */
    public function testLinksIsCallable(): void
    {
        $this->assertArrayHasKey('links', $this->section['computed']);
        $this->assertIsCallable($this->section['computed']['links']);
    }

    /**
     * The headline prop defaults to the file-archive wording.
     */
    public function testHeadlineDefault(): void
    {
        $this->assertSame('Linked from', ($this->section['props']['headline'])());
    }

    /**
     * The headline prop returns a caller-supplied override (e.g. Image Bank).
     */
    public function testHeadlineOverride(): void
    {
        $this->assertSame(
            'Used on these pages',
            ($this->section['props']['headline'])('Used on these pages')
        );
    }

    /**
     * The emptyText prop defaults to the file-archive wording.
     */
    public function testEmptyTextDefault(): void
    {
        $this->assertSame(
            'No pages link to this file.',
            ($this->section['props']['emptyText'])()
        );
    }

    /**
     * The emptyText prop returns a caller-supplied override (e.g. Image Bank).
     */
    public function testEmptyTextOverride(): void
    {
        $this->assertSame(
            'No pages use this image.',
            ($this->section['props']['emptyText'])('No pages use this image.')
        );
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
