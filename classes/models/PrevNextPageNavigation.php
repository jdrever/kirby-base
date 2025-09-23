<?php
namespace BSBI\WebBase\models;


/**
 *
 */
class PrevNextPageNavigation
{
    private string $previousPageLink;
    private string $previousPageTitle;
    private string $nextPageLink;
    private string $nextPageTitle;

    /**
     * @return bool
     */
    public function hasNavigation(): bool
    {
        return !empty($this->previousPageLink) && !empty($this->nextPageLink);
    }

    /**
     * @return bool
     */
    public function hasPreviousPage(): bool {
        return !empty($this->previousPageLink);
    }

    /**
     * @return string
     */
    public function getPreviousPageLink(): string
    {
        return $this->previousPageLink;
    }

    /**
     * @param string $previousPageLink
     * @return void
     */
    public function setPreviousPageLink(string $previousPageLink): void
    {
        $this->previousPageLink = $previousPageLink;
    }

    /**
     * @return string
     */
    public function getPreviousPageTitle(): string
    {
        return $this->previousPageTitle;
    }

    /**
     * @param string $previousPageTitle
     * @return void
     */
    public function setPreviousPageTitle(string $previousPageTitle): void
    {
        $this->previousPageTitle = $previousPageTitle;
    }

    /**
     * @return string
     */
    public function getNextPageLink(): string
    {
        return $this->nextPageLink;
    }

    /**
     * @param string $nextPageLink
     * @return void
     */
    public function setNextPageLink(string $nextPageLink): void
    {
        $this->nextPageLink = $nextPageLink;
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool {
        return !empty($this->nextPageLink);
    }

    /**
     * @return string
     */
    public function getNextPageTitle(): string
    {
        return $this->nextPageTitle;
    }

    /**
     * @param string $nextPageTitle
     * @return void
     */
    public function setNextPageTitle(string $nextPageTitle): void
    {
        $this->nextPageTitle = $nextPageTitle;
    }

}