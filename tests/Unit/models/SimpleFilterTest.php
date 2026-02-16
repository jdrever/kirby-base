<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\SimpleFilter;
use PHPUnit\Framework\TestCase;

final class SimpleFilterTest extends TestCase
{
    private function createFilter(): SimpleFilter
    {
        return new SimpleFilter();
    }

    public function testDescriptionDefaultsToEmpty(): void
    {
        $filter = $this->createFilter();

        $this->assertFalse($filter->hasDescription());
    }

    public function testAddToDescriptionAccumulates(): void
    {
        $filter = $this->createFilter();
        $filter->addToDescription('Filtered by type');
        $filter->addToDescription('Sorted by date');

        $this->assertTrue($filter->hasDescription());
        $this->assertCount(2, $filter->getDescription());
        $this->assertSame('Filtered by type', $filter->getDescription()[0]);
    }

    public function testKeywordsGetterSetter(): void
    {
        $filter = $this->createFilter();

        $this->assertFalse($filter->hasKeywords());

        $filter->setKeywords('nature wildlife');
        $this->assertTrue($filter->hasKeywords());
        $this->assertSame('nature wildlife', $filter->getKeywords());
    }

    public function testStopPaginationGetterSetter(): void
    {
        $filter = $this->createFilter();

        $this->assertFalse($filter->doStopPagination());

        $filter->setStopPagination(true);
        $this->assertTrue($filter->doStopPagination());
    }

    public function testInheritsErrorHandling(): void
    {
        $filter = $this->createFilter();

        $filter->recordError('Filter failed');
        $this->assertTrue($filter->hasErrors());
        $this->assertSame('Filter failed', $filter->getFirstErrorMessage());
    }
}
