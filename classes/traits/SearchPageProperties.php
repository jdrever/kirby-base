<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\WebPageLinks;

/**
 *
 */
trait SearchPageProperties
{
    private WebPageLinks $searchResults;

    private array $contentTypeOptions;

    private string $selectedContentType = '';

    private string $specialSearchType = '';

    private bool $searchCompleted = false;

    /**
     * @return bool
     */
    public function hasSearchResults(): bool
    {
        return isset($this->searchResults) && $this->searchResults->count() > 0;
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

    public function hasContentTypeOptions(): bool {
        return isset($this->contentTypeOptions) && count($this->contentTypeOptions) > 0;
    }

    /**
     * @return array
     */
    public function getContentTypeOptions(): array
    {
        return $this->contentTypeOptions;
    }

    /**
     * @param array $contentTypeOptions
     * @return $this
     */
    public function setContentTypeOptions(array $contentTypeOptions): static
    {
        $this->contentTypeOptions = $contentTypeOptions;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelectedContentType(): string
    {
        return $this->selectedContentType;
    }

    /**
     * @param string $selectedContentType
     * @return $this
     */
    public function setSelectedContentType(string $selectedContentType): static
    {
        $this->selectedContentType = $selectedContentType;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSearchCompleted(): bool
    {
        return $this->searchCompleted;
    }

    /**
     * @param bool $searchCompleted
     * @return $this
     */
    public function setSearchCompleted(bool $searchCompleted): static
    {
        $this->searchCompleted = $searchCompleted;
        return $this;
    }

}