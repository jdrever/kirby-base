<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ListHandling;
use BSBI\WebBase\traits\ErrorHandling;


/**
 * Class WebPageBlocks
 * Represents a list of web pages blocks with various properties and methods.
 * @package BSBI\Web
 */
class WebPageBlocks
{
    /**
     * @use ListHandling<WebPageBlock, BaseFilter>
     */
    use ListHandling;
    use ErrorHandling;
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
    public function getBlocks(): array {
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

}
