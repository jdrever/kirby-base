<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Pagination;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pagination::appendQueryParams.
 */
final class PaginationAppendQueryParamsTest extends TestCase
{
    private Pagination $pagination;

    protected function setUp(): void
    {
        $this->pagination = new Pagination();
        $this->pagination
            ->setCurrentPage(2)
            ->setPageCount(3)
            ->setHasPreviousPage(true)
            ->setPreviousPageUrl('https://example.com/rpr?page=1')
            ->setHasNextPage(true)
            ->setNextPageUrl('https://example.com/rpr?page=3');

        $this->pagination->addPageUrl('https://example.com/rpr?page=1');
        $this->pagination->addPageUrl('https://example.com/rpr?page=2');
        $this->pagination->addPageUrl('https://example.com/rpr?page=3');
    }

    public function testAppendQueryParamsAddsParamsToAllUrls(): void
    {
        $this->pagination->appendQueryParams('sort_by=date&sort_dir=desc');

        $this->assertSame(
            'https://example.com/rpr?page=1&sort_by=date&sort_dir=desc',
            $this->pagination->getPreviousPageUrl()
        );
        $this->assertSame(
            'https://example.com/rpr?page=3&sort_by=date&sort_dir=desc',
            $this->pagination->getNextPageUrl()
        );
        $this->assertSame(
            'https://example.com/rpr?page=1&sort_by=date&sort_dir=desc',
            $this->pagination->getPageUrl(1)
        );
        $this->assertSame(
            'https://example.com/rpr?page=2&sort_by=date&sort_dir=desc',
            $this->pagination->getPageUrl(2)
        );
        $this->assertSame(
            'https://example.com/rpr?page=3&sort_by=date&sort_dir=desc',
            $this->pagination->getPageUrl(3)
        );
    }

    public function testAppendQueryParamsUsesQuestionMarkWhenNoExistingQueryString(): void
    {
        $pagination = new Pagination();
        $pagination
            ->setCurrentPage(1)
            ->setPageCount(2)
            ->setHasPreviousPage(false)
            ->setPreviousPageUrl('')
            ->setHasNextPage(true)
            ->setNextPageUrl('https://example.com/rpr/page:2');

        $pagination->addPageUrl('https://example.com/rpr');
        $pagination->addPageUrl('https://example.com/rpr/page:2');

        $pagination->appendQueryParams('sort_by=title&sort_dir=asc');

        $this->assertSame(
            'https://example.com/rpr/page:2?sort_by=title&sort_dir=asc',
            $pagination->getNextPageUrl()
        );
        $this->assertSame(
            'https://example.com/rpr?sort_by=title&sort_dir=asc',
            $pagination->getPageUrl(1)
        );
    }

    public function testAppendQueryParamsIgnoresEmptyUrls(): void
    {
        $pagination = new Pagination();
        $pagination
            ->setCurrentPage(1)
            ->setPageCount(1)
            ->setHasPreviousPage(false)
            ->setPreviousPageUrl('')
            ->setHasNextPage(false)
            ->setNextPageUrl('');

        $pagination->addPageUrl('https://example.com/rpr');

        $pagination->appendQueryParams('sort_by=date&sort_dir=asc');

        $this->assertSame('', $pagination->getPreviousPageUrl());
        $this->assertSame('', $pagination->getNextPageUrl());
    }

    public function testAppendQueryParamsWithEmptyParamsIsNoop(): void
    {
        $this->pagination->appendQueryParams('');

        $this->assertSame(
            'https://example.com/rpr?page=1',
            $this->pagination->getPreviousPageUrl()
        );
        $this->assertSame(
            'https://example.com/rpr?page=1',
            $this->pagination->getPageUrl(1)
        );
    }

    public function testAppendQueryParamsReturnsSelf(): void
    {
        $result = $this->pagination->appendQueryParams('sort_by=date&sort_dir=desc');
        $this->assertSame($this->pagination, $result);
    }
}
