<?php

namespace BSBI\WebBase\models;


/**
 * Class WebPageLinks
 * Represents a list of web pages with various properties and methods.
 * @package BSBI\Web
 */
class WebPageTagLinkSet extends BaseModel
{

    private string $tagType;

    private WebPageLinks $tagLinks;

    /**
     * @return bool
     */
    public function hasLinks(): bool
    {
        return isset($this->tagLinks) && $this->tagLinks->count() > 0;
    }

    /**
     * @return WebPageLinks
     */
    public function getLinks(): WebPageLinks
    {
        return $this->tagLinks;
    }

    /**
     * @param WebPageLinks $tagLinks
     * @return $this
     */
    public function setLinks(WebPageLinks $tagLinks): WebPageTagLinkSet
    {
        $this->tagLinks = $tagLinks;
        return $this;
    }

    /**
     * @return string
     */
    public function getTagType(): string
    {
        return $this->tagType;
    }

    /**
     * @param string $tagType
     * @return $this
     */
    public function setTagType(string $tagType): WebPageTagLinkSet
    {
        $this->tagType = $tagType;
        return $this;
    }


}