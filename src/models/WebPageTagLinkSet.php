<?php

namespace BSBI\WebBase\models;

use BSBI\Web\models\WebPage;
use BSBI\WebBase\interfaces\ListHandler;
use BSBI\WebBase\traits\ListHandling;
use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class WebPageLinks
 * Represents a list of web pages with various properties and methods.
 * @package BSBI\Web
 */
class WebPageTagLinkSet extends BaseModel
{

    private string $tagType;

    private WebPageLinks $tagLinks;

    public function hasLinks(): bool
    {
        return isset($this->tagLinks) && $this->tagLinks->count() > 0;
    }

    public function getLinks(): WebPageLinks
    {
        return $this->tagLinks;
    }

    public function setLinks(WebPageLinks $tagLinks): WebPageTagLinkSet
    {
        $this->tagLinks = $tagLinks;
        return $this;
    }

    public function getTagType(): string
    {
        return $this->tagType;
    }

    public function setTagType(string $tagType): WebPageTagLinkSet
    {
        $this->tagType = $tagType;
        return $this;
    }


}