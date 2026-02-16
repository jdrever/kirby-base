<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\SimpleFilter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the SimpleFilter model (concrete subclass of BaseFilter).
 *
 * Covers description accumulation, keywords getter/setter,
 * stop-pagination flag, and inherited ErrorHandling trait behaviour.
 */
final class SimpleFilterTest extends TestCase
{
    /**
     * Create a SimpleFilter instance for testing.
     *
     * @return SimpleFilter
     */
    private function createFilter(): SimpleFilter
    {
        return new SimpleFilter();
    }

    /**
     * Verify description defaults to empty (unset).
     */
    public function testDescriptionDefaultsToEmpty(): void
    {
        $filter = $this->createFilter();

        $this->assertFalse($filter->hasDescription());
    }

    /**
     * Verify addToDescription() accumulates description entries.
     */
    public function testAddToDescriptionAccumulates(): void
    {
        $filter = $this->createFilter();
        $filter->addToDescription('Filtered by type');
        $filter->addToDescription('Sorted by date');

        $this->assertTrue($filter->hasDescription());
        $this->assertCount(2, $filter->getDescription());
        $this->assertSame('Filtered by type', $filter->getDescription()[0]);
    }

    /**
     * Verify keywords default to empty and can be set.
     */
    public function testKeywordsGetterSetter(): void
    {
        $filter = $this->createFilter();

        $this->assertFalse($filter->hasKeywords());

        $filter->setKeywords('nature wildlife');
        $this->assertTrue($filter->hasKeywords());
        $this->assertSame('nature wildlife', $filter->getKeywords());
    }

    /**
     * Verify stop-pagination flag defaults to false and can be toggled.
     */
    public function testStopPaginationGetterSetter(): void
    {
        $filter = $this->createFilter();

        $this->assertFalse($filter->doStopPagination());

        $filter->setStopPagination(true);
        $this->assertTrue($filter->doStopPagination());
    }

    /**
     * Verify ErrorHandling trait methods are available via BaseFilter.
     */
    public function testInheritsErrorHandling(): void
    {
        $filter = $this->createFilter();

        $filter->recordError('Filter failed');
        $this->assertTrue($filter->hasErrors());
        $this->assertSame('Filter failed', $filter->getFirstErrorMessage());
    }
}
