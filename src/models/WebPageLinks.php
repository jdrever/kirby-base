<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ListHandling;
use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class WebPageLinks
 * Represents a list of web pages with various properties and methods.
 * @package BSBI\Web
 */
class WebPageLinks
{
    /**
     * @use ListHandling<WebPageLink, BaseFilter>
     */
    use ListHandling;
    use ErrorHandling;

    public function __construct()
    {
        $this->status = true;
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

    /**
     * @return WebPageLink[]
     */
    public function getLinks(): array {
        return $this->list;
    }

}
