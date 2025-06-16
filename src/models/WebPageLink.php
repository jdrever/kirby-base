<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\CoreWebPageProperties;

/**
 * Class WebPageLink
 * Represents a simple web page link
 *
 * @package BSBI\Web
 */
class WebPageLink extends BaseModel
{
    use CoreWebPageProperties;

    private Image $image;

    private bool $excludeFromMenus = false;

    /**
     * @var string
     */
    private string $linkDescription;

    private string $requirements;

    /**
     * @param string $title
     * @param string $url
     * @param string $pageId
     * @param string $pageType
     */
    public function __construct(string $title, string $url, string $pageId, string $pageType)
    {
        $this->pageId = $pageId;
        $this->pageType = $pageType;
        parent::__construct($title, $url);

    }

    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return isset($this->image);
    }

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function setImage(Image $image): WebPageLink
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getLinkDescription(): string
    {
        return $this->linkDescription;
    }

    /**
     * @param string $linkDescription
     * @return $this
     */
    public function setLinkDescription(string $linkDescription): WebPageLink
    {
        $this->linkDescription = $linkDescription;
        return $this;
    }

    public function isExcludeFromMenus(): bool
    {
        return $this->excludeFromMenus;
    }

    public function setExcludeFromMenus(bool $excludeFromMenus): WebPageLink
    {
        $this->excludeFromMenus = $excludeFromMenus;
        return $this;
    }

    public function getPageType(): string
    {
        return $this->pageType;
    }

    public function setPageType(string $pageType): WebPageLink
    {
        $this->pageType = $pageType;
        return $this;
    }

    public function hasRequirements(): bool
    {
        return !empty($this->requirements);
    }

    public function getRequirements(): string
    {
        return $this->requirements;
    }

    public function setRequirements(string $requirements): WebPageLink
    {
        $this->requirements = $requirements;
        return $this;
    }
    
}
