<?php

namespace BSBI\WebBase\models;

/**
 */
class WebPageTagLinks extends BaseList
{


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

    /**
     * @return string
     */
    function getItemType(): string
    {
        return WebPageTagLinkSet::class;
    }

    /**
     * @return string
     */
    function getFilterType(): string
    {
        return BaseFilter::class;
    }
}