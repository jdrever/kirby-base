<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\interfaces\ListHandler;
use BSBI\WebBase\traits\ListHandling;
use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class WebPageLinks
 * Represents a list of web pages with various properties and methods.
 * @package BSBI\Web
 */
class WebPageTagLinkSet extends BaseModel implements ListHandler
{
    /**
     * @use ListHandling<WebPageLink, BaseFilter>
     */
    use ListHandling;

    private string $tagType;

    /**
     * @param WebPageLink $item
     * @return $this
     */
    public function addListItem(WebPageLink $item): self {
        $this->add($item);
        return $this;
    }

    /**
     * @return WebPageLinks[]
     */
    public function getListItems(): array
    {
        return $this->list;
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