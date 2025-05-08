<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\WebPageLinks;

trait SearchPageProperties
{
    private WebPageLinks $searchResults;

    public function hasSearchResults(): bool
    {
        return $this->searchResults->count() > 0;
    }

    public function getSearchResults(): WebPageLinks
    {
        return $this->searchResults;
    }

    public function setSearchResults(WebPageLinks $searchResults): self
    {
        $this->searchResults = $searchResults;
        return $this;
    }
}