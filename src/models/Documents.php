<?php

namespace BSBI\WebBase\models;


/**
 * @package BSBI\Web
 */
class Documents extends BaseList
{

    /**
     * Add a link
     * @param Document $link
     */
    public function addListItem(Document $link): void
    {
        $this->add($link);
    }


    /**
     * @return Document[]
     */
    public function getListItems(): array {
        return $this->list;
    }

    function getItemType(): string
    {
        return Document::class;
    }

    function getFilterType(): string
    {
        return BaseFilter::class;
    }
}
