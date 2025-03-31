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
class WebPageTagLinks implements ListHandler
{
    /**
     * @use ListHandling<WebPageTagLinkSet, BaseFilter>
     */
    use ListHandling;

    public function addListItem(WebPageTagLinkSet $item): self {
        $this->add($item);
        return $this;
    }

    /**
     * @return WebPageTagLinkSet[]
     */
    public function getListItems(): array
    {
        return $this->list;
    }



}