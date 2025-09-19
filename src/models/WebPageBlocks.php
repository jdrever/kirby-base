<?php

namespace BSBI\WebBase\models;


class WebPageBlocks extends BaseList
{

    /**
     * Add a web page
     * @param WebPageBlock $block
     */
    public function addListItem(WebPageBlock $block): void
    {
        $this->add($block);
    }

    /**
     * @return WebPageBlock[]
     */
    public function getListItems(): array {
        return $this->list;
    }

    /**
     * Do the blocks contain a block of a specified type?
     * @param string $blockType
     * @return bool
     */
    public function hasBlockOfType(string $blockType): bool
    {
        $matchingBlock = array_filter($this->list, function ($item) use ($blockType) {
            if ($item instanceof WebPageBlock) {
                return $item->getBlockType() === $blockType;
            }
            return false;
        });
        return count($matchingBlock) > 0;
    }

    /**
     * @return string
     */
    public function getAllContentAsHTML(): string {
        $html = '';
        foreach ($this->list as $block) {
            $html.= $block->getBlockContent();
        }
        return $html;
    }

    function getItemType(): string
    {
        return WebPageBlock::class;
    }

    function getFilterType(): string
    {
        return BaseFilter::class;
    }
}
