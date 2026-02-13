<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Pagination;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    private function createPagination(int $currentPage = 1, int $pageCount = 5): Pagination
    {
        $pagination = new Pagination();
        $pagination->setCurrentPage($currentPage);
        $pagination->setPageCount($pageCount);
        $pagination->setHasPreviousPage($currentPage > 1);
        $pagination->setHasNextPage($currentPage < $pageCount);

        if ($currentPage > 1) {
            $pagination->setPreviousPageUrl('/page/' . ($currentPage - 1));
        }
        if ($currentPage < $pageCount) {
            $pagination->setNextPageUrl('/page/' . ($currentPage + 1));
        }

        return $pagination;
    }

    public function testFirstPageHasNoPrevious(): void
    {
        $pagination = $this->createPagination(1, 5);

        $this->assertFalse($pagination->hasPreviousPage());
        $this->assertTrue($pagination->hasNextPage());
        $this->assertSame('/page/2', $pagination->getNextPageUrl());
    }

    public function testMiddlePageHasBothNavigation(): void
    {
        $pagination = $this->createPagination(3, 5);

        $this->assertTrue($pagination->hasPreviousPage());
        $this->assertTrue($pagination->hasNextPage());
        $this->assertSame('/page/2', $pagination->getPreviousPageUrl());
        $this->assertSame('/page/4', $pagination->getNextPageUrl());
    }

    public function testLastPageHasNoNext(): void
    {
        $pagination = $this->createPagination(5, 5);

        $this->assertTrue($pagination->hasPreviousPage());
        $this->assertFalse($pagination->hasNextPage());
    }

    public function testPageUrls(): void
    {
        $pagination = new Pagination();
        $pagination->addPageUrl('/page/1');
        $pagination->addPageUrl('/page/2');
        $pagination->addPageUrl('/page/3');

        $this->assertSame('/page/1', $pagination->getPageUrl(1));
        $this->assertSame('/page/2', $pagination->getPageUrl(2));
        $this->assertSame('/page/3', $pagination->getPageUrl(3));
    }
}
