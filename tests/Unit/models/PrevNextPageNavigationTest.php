<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\PrevNextPageNavigation;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the PrevNextPageNavigation model.
 *
 * Covers navigation availability checks (requires both links),
 * individual previous/next page detection, getter values,
 * and default empty state.
 */
final class PrevNextPageNavigationTest extends TestCase
{
    /**
     * Create a PrevNextPageNavigation with both links populated.
     *
     * @param string $prevLink  The previous page URL
     * @param string $prevTitle The previous page title
     * @param string $nextLink  The next page URL
     * @param string $nextTitle The next page title
     * @return PrevNextPageNavigation
     */
    private function createNavigation(
        string $prevLink = '/prev',
        string $prevTitle = 'Previous',
        string $nextLink = '/next',
        string $nextTitle = 'Next',
    ): PrevNextPageNavigation {
        $nav = new PrevNextPageNavigation();
        $nav->setPreviousPageLink($prevLink);
        $nav->setPreviousPageTitle($prevTitle);
        $nav->setNextPageLink($nextLink);
        $nav->setNextPageTitle($nextTitle);
        return $nav;
    }

    /**
     * Verify hasNavigation() returns true when both previous and next links are set.
     */
    public function testHasNavigationRequiresBothLinks(): void
    {
        $nav = $this->createNavigation();

        $this->assertTrue($nav->hasNavigation());
        $this->assertTrue($nav->hasPreviousPage());
        $this->assertTrue($nav->hasNextPage());
    }

    /**
     * Verify hasNavigation() returns false when only the previous link is set.
     */
    public function testHasNavigationFalseWhenOnlyPreviousSet(): void
    {
        $nav = new PrevNextPageNavigation();
        $nav->setPreviousPageLink('/prev');
        $nav->setPreviousPageTitle('Previous');

        $this->assertFalse($nav->hasNavigation());
        $this->assertTrue($nav->hasPreviousPage());
    }

    /**
     * Verify all getters return the values that were set.
     */
    public function testGettersReturnSetValues(): void
    {
        $nav = $this->createNavigation('/page-1', 'Page One', '/page-3', 'Page Three');

        $this->assertSame('/page-1', $nav->getPreviousPageLink());
        $this->assertSame('Page One', $nav->getPreviousPageTitle());
        $this->assertSame('/page-3', $nav->getNextPageLink());
        $this->assertSame('Page Three', $nav->getNextPageTitle());
    }

    /**
     * Verify a new PrevNextPageNavigation has no navigation by default.
     */
    public function testNewNavigationHasNoPages(): void
    {
        $nav = new PrevNextPageNavigation();

        $this->assertFalse($nav->hasNavigation());
    }
}
