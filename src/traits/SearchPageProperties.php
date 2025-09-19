<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\WebPageLinks;

/**
 *
 */
trait SearchPageProperties
{
    private WebPageLinks $searchResults;

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
     * @return SearchPageProperties
     */
    public function setSearchResults(WebPageLinks $searchResults): self
    {
        $this->searchResults = $searchResults;
        return $this;
    }
}