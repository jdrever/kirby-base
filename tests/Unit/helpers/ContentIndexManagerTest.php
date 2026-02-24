<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\ContentIndexDefinition;
use BSBI\WebBase\helpers\ContentIndexRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ContentIndexDefinition contract and ContentIndexRegistry.
 *
 * ContentIndexManager requires a Kirby environment for database initialization,
 * so we test the definition contract and registry logic here. Manager integration
 * is tested at the site level.
 */
final class ContentIndexManagerTest extends TestCase
{
    protected function setUp(): void
    {
        ContentIndexRegistry::clear();
    }

    /**
     * Create a concrete test definition that extends the abstract base.
     */
    private function createTestDefinition(string $name = 'test', array $templates = ['test_page']): ContentIndexDefinition
    {
        return new class ($name, $templates) extends ContentIndexDefinition {
            public function __construct(
                private readonly string $name,
                private readonly array $templates
            ) {
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getCollectionName(): string
            {
                return 'testPages';
            }

            public function getTemplates(): array
            {
                return $this->templates;
            }

            public function getColumns(): array
            {
                return [
                    'title' => 'TEXT NOT NULL DEFAULT ""',
                    'start_date' => 'TEXT NOT NULL DEFAULT ""',
                ];
            }

            public function getIndexes(): array
            {
                return [
                    'CREATE INDEX IF NOT EXISTS idx_test_start_date ON content_test (start_date)',
                ];
            }

            public function getRowData(\Kirby\Cms\Page $page, \BSBI\WebBase\helpers\KirbyBaseHelper $helper): array
            {
                return [
                    'page_id' => $page->id(),
                    'title' => 'Test',
                    'start_date' => '2025-01-01',
                ];
            }
        };
    }

    public function testDefinitionReturnsName(): void
    {
        $def = $this->createTestDefinition('events');
        $this->assertSame('events', $def->getName());
    }

    public function testDefinitionReturnsCollectionName(): void
    {
        $def = $this->createTestDefinition();
        $this->assertSame('testPages', $def->getCollectionName());
    }

    public function testDefinitionReturnsTemplates(): void
    {
        $def = $this->createTestDefinition('events', ['event', 'annual_event']);
        $this->assertSame(['event', 'annual_event'], $def->getTemplates());
    }

    public function testDefinitionReturnsColumns(): void
    {
        $def = $this->createTestDefinition();
        $columns = $def->getColumns();

        $this->assertArrayHasKey('title', $columns);
        $this->assertArrayHasKey('start_date', $columns);
    }

    public function testDefinitionReturnsIndexes(): void
    {
        $def = $this->createTestDefinition();
        $indexes = $def->getIndexes();

        $this->assertCount(1, $indexes);
        $this->assertStringContainsString('CREATE INDEX', $indexes[0]);
    }

    // --- Registry tests (no Kirby required) ---

    public function testRegistryGetReturnsNullForUnregistered(): void
    {
        $this->assertNull(ContentIndexRegistry::get('nonexistent'));
    }

    public function testRegistryAllIsEmptyByDefault(): void
    {
        $this->assertEmpty(ContentIndexRegistry::all());
    }

    public function testRegistryClear(): void
    {
        // Just verify clear doesn't throw when empty
        ContentIndexRegistry::clear();
        $this->assertEmpty(ContentIndexRegistry::all());
    }

    public function testRegistryGetManagersForTemplateReturnsEmptyForUnknownTemplate(): void
    {
        $managers = ContentIndexRegistry::getManagersForTemplate('unknown_template');
        $this->assertEmpty($managers);
    }
}
