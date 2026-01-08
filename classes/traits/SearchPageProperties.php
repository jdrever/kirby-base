<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\WebPageLinks;

/**
 *
 */
trait SearchPageProperties
{
    private WebPageLinks $searchResults;

    private string $specialSearchType = '';

    /**
     * @return bool
     */
    public function hasSearchResults(): bool
    {
        return $this->searchResults->count() > 0;
    }

    /**
     * @return WebPageLinks
     */
    public function getSearchResults(): WebPageLinks
    {
        return $this->searchResults;
    }

    /**
     * @param WebPageLinks $searchResults
     * @return $this
     */
    public function setSearchResults(WebPageLinks $searchResults): static
    {
        $this->searchResults = $searchResults;
        return $this;
    }

    public function hasSpecialSearchType(): bool {
        return !empty($this->specialSearchType);
    }

    /**
     * @return string
     */
    public function getSpecialSearchType(): string
    {
        return $this->specialSearchType;
    }

    /**
     * @param string $specialSearchType
     * @return $this
     */
    public function setSpecialSearchType(string $specialSearchType): static
    {
        $this->specialSearchType = $specialSearchType;
        return $this;
    }
}