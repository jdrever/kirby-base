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
    /** @var bool has a previous page? */
    private bool $hasPreviousPage;

    /** @var string The previous page url */
    private string $previousPageUrl;

    private int $currentPage;

    private int $pageCount;

    private array $pageUrls;

    /** @var bool has a next page? */
    private bool $hasNextPage;

    /** @var string The next page url */
    private string $nextPageUrl;


    /**
     * @return bool
     */
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

    /**
     * @param string $previousPageUrl
     * @return $this
     */
    public function setPreviousPageUrl(string $previousPageUrl): Pagination
    {
        $this->previousPageUrl = $previousPageUrl;
        return $this;
    }

    /**
     * @param string $pageUrl
     * @return $this
     */
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

    /**
     * @return bool
     */
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

    /**
     * @param bool $hasPreviousPage
     * @return $this
     */
    public function setHasPreviousPage(bool $hasPreviousPage): Pagination
    {
        $this->hasPreviousPage = $hasPreviousPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }


    /**
     * @param int $page
     * @return $this
     */
    public function setCurrentPage(int $page): Pagination
    {
        $this->currentPage = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }


    /**
     * @param int $pages
     * @return $this
     */
    public function setPageCount(int $pages): Pagination
    {
        $this->pageCount = $pages;
        return $this;
    }

    /**
     * @param bool $hasNextPage
     * @return $this
     */
    public function setHasNextPage(bool $hasNextPage): Pagination
    {
        $this->hasNextPage = $hasNextPage;
        return $this;
    }

    /**
     * @param string $nextPageUrl
     * @return $this
     */
    public function setNextPageUrl(string $nextPageUrl): Pagination
    {
        $this->nextPageUrl = $nextPageUrl;
        return $this;
    }


}

