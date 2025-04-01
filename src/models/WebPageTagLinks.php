<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\interfaces\ListHandler;
use BSBI\WebBase\traits\ListHandling;

/**
 */
class WebPageTagLinks extends BaseModel implements ListHandler
{
    /**
     * @use ListHandling<WebPageTagLinkSet,BaseFilter>
     */
    use ListHandling;

    /**
     * @param WebPageTagLinkSet $item
     * @return $this
     */
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