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
class WebPageLinks extends BaseModel implements ListHandler
{
    /**
     * @use ListHandling<WebPageLink, BaseFilter>
     */
    use ListHandling;
    use ErrorHandling;

    public function __construct()
    {
        $this->status = true;
        parent::__construct('WebPageLinks', '');
    }


    /**
     * Add a web page
     * @param WebPageLink $link
     */
    public function addListItem(WebPageLink $link): self
    {
        if ($link->didComplete()) {
            $this->add($link);
        }
        return $this;
    }

    //TODO: update this class to use ListHandler (would need to update all projects)
    /**
     * @return WebPageLink[]
     */
    public function getListItems(): array {
        return $this->list;
    }

    /**
     * Find the page with the matching link type
     * @param string $linkType
     * @return WebPageLink
     */
    public function getLink(string $linkType): WebPageLink
    {
        $matchingLink = array_filter($this->list, function ($item) use ($linkType) {
            if ($item instanceof WebPageLink) {
                return $item->getPageType() === $linkType;
            }
            return false;
        });

        if (count($matchingLink) === 0) {
            $linkNotFound = new WebPageLink('','', 'NOT_FOUND', 'NOT_FOUND');
            $linkNotFound->setStatus(false);
            return $linkNotFound;
        }
        return reset($matchingLink);
    }

}
