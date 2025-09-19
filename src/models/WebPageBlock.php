<?php

namespace models;

namespace BSBI\WebBase\models;

/**
 * Class Page
 * Represents a web page
 *
 * @package BSBI\Web
 */
class WebPageBlock extends BaseModel
{
    /**
     * @var string
     */
    private string $blockType;

    /**
     * @var string
     */
    private string $blockLevel;

    /**
     * @var string
     */
    private string $blockContent;

    /**
     * @var string
     */
    private string $anchor;

    /**
     * @param string $blockType
     * @param string $blockContent
     */
    public function __construct(string $blockType, string $blockContent)
    {
        $this->blockType = $blockType;
        $this->blockContent = $blockContent;
        parent::__construct('');
    }

    /**
     * @return string
     */
    public function getBlockType(): string
    {
        return $this->blockType;
    }

    /**
     * @param string $blockType
     * @return $this
     */
    public function setBlockType(string $blockType): WebPageBlock
    {
        $this->blockType = $blockType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockLevel(): string
    {
        return $this->blockLevel;
    }

    /**
     * @param string $blockLevel
     * @return $this
     */
    public function setBlockLevel(string $blockLevel): WebPageBlock
    {
        $this->blockLevel = $blockLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockContent(): string
    {
        return $this->blockContent;
    }

    /**
     * @param string $blockContent
     * @return $this
     */
    public function setBlockContent(string $blockContent): WebPageBlock
    {
        $this->blockContent = $blockContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnchor(): string
    {
        return $this->anchor;
    }

    /**
     * @param string $anchor
     * @return $this
     */
    public function setAnchor(string $anchor): WebPageBlock
    {
        $this->anchor = $anchor;
        return $this;
    }



}