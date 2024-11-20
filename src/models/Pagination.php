<?php

namespace BSBI\WebBase\models;

/**
 * Class Pagination
 * Represents the pagination for a list
 *
 * @package BSBI\Web
 */
class Pagination
{
    /** @var bool has previous page? */
    private bool $hasPreviousPage;

    /** @var string The previous page url */
    private string $previousPageUrl;

    private int $currentPage;

    private int $pageCount;

    private array $pageUrls;

    /** @var bool has next page? */
    private bool $hasNextPage;

    /** @var string The next page url */
    private string $nextPageUrl;


    public function hasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }


    /**
     * @return string
     */
    public function getPreviousPageUrl(): string
    {
        return $this->previousPageUrl;
    }

    public function setPreviousPageUrl(string $previousPageUrl): Pagination
    {
        $this->previousPageUrl = $previousPageUrl;
        return $this;
    }

    public function addPageUrl(string $pageUrl): Pagination {
        $this->pageUrls[] = $pageUrl;
        return $this;
    }


    /**
     * @param int $pageNumber
     * @return string
     */
    public function getPageUrl(int $pageNumber) : string {
        return $this->pageUrls[$pageNumber-1];
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    /**
     * @return string
     */
    public function getNextPageUrl(): string
    {
        return $this->nextPageUrl;
    }

    public function setHasPreviousPage(bool $hasPreviousPage): Pagination
    {
        $this->hasPreviousPage = $hasPreviousPage;
        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }



    public function setCurrentPage(int $page): Pagination
    {
        $this->currentPage = $page;
        return $this;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }



    public function setPageCount(int $pages): Pagination
    {
        $this->pageCount = $pages;
        return $this;
    }

    public function setHasNextPage(bool $hasNextPage): Pagination
    {
        $this->hasNextPage = $hasNextPage;
        return $this;
    }

    public function setNextPageUrl(string $nextPageUrl): Pagination
    {
        $this->nextPageUrl = $nextPageUrl;
        return $this;
    }


}

