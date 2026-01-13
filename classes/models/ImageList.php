<?php

namespace BSBI\WebBase\models;

/**
 */
class ImageList extends BaseList
{

    /**
     * @return Image[]
     */
    public function getListItems(): array
    {
        return $this->list;
    }

    /**
     * @param Image $image
     */
    public function addListItem(Image $image): void
    {
        $this->add($image);
    }

    /**
     * @return string
     */
    function getItemType(): string
    {
        return Image::class;
    }

    /**
     * @return string
     */
    function getFilterType(): string
    {
        return BaseFilter::class;
    }
}
