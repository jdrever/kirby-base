<?php
namespace BSBI\WebBase\models;


class PrevNextPageNavigation
{
    private string $previousPageLink;
    private string $previousPageTitle;
    private string $nextPageLink;
    private string $nextPageTitle;

    public function hasNavigation(): bool
    {
        return !empty($this->previousPageLink) && !empty($this->nextPageLink);
    }

    public function hasPreviousPage(): bool {
        return !empty($this->previousPageLink);
    }

    public function getPreviousPageLink(): string
    {
        return $this->previousPageLink;
    }

    public function setPreviousPageLink(string $previousPageLink): void
    {
        $this->previousPageLink = $previousPageLink;
    }

    public function getPreviousPageTitle(): string
    {
        return $this->previousPageTitle;
    }

    public function setPreviousPageTitle(string $previousPageTitle): void
    {
        $this->previousPageTitle = $previousPageTitle;
    }

    public function getNextPageLink(): string
    {
        return $this->nextPageLink;
    }

    public function setNextPageLink(string $nextPageLink): void
    {
        $this->nextPageLink = $nextPageLink;
    }

    public function hasNextPage(): bool {
        return !empty($this->nextPageLink);
    }

    public function getNextPageTitle(): string
    {
        return $this->nextPageTitle;
    }

    public function setNextPageTitle(string $nextPageTitle): void
    {
        $this->nextPageTitle = $nextPageTitle;
    }

}