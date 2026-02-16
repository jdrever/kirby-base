<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\PrevNextPageNavigation;
use PHPUnit\Framework\TestCase;

final class PrevNextPageNavigationTest extends TestCase
{
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

    public function testHasNavigationRequiresBothLinks(): void
    {
        $nav = $this->createNavigation();

        $this->assertTrue($nav->hasNavigation());
        $this->assertTrue($nav->hasPreviousPage());
        $this->assertTrue($nav->hasNextPage());
    }

    public function testHasNavigationFalseWhenOnlyPreviousSet(): void
    {
        $nav = new PrevNextPageNavigation();
        $nav->setPreviousPageLink('/prev');
        $nav->setPreviousPageTitle('Previous');

        $this->assertFalse($nav->hasNavigation());
        $this->assertTrue($nav->hasPreviousPage());
    }

    public function testGettersReturnSetValues(): void
    {
        $nav = $this->createNavigation('/page-1', 'Page One', '/page-3', 'Page Three');

        $this->assertSame('/page-1', $nav->getPreviousPageLink());
        $this->assertSame('Page One', $nav->getPreviousPageTitle());
        $this->assertSame('/page-3', $nav->getNextPageLink());
        $this->assertSame('Page Three', $nav->getNextPageTitle());
    }

    public function testNewNavigationHasNoPages(): void
    {
        $nav = new PrevNextPageNavigation();

        $this->assertFalse($nav->hasNavigation());
    }
}
