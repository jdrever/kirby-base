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

    /**
     * @var string
     */
    private string $linkDescription = '';

    private string $requirements;

    private string $breadcrumb;

    private bool $showSubPageImages = false;

    private bool $excludeFromMenus = false;

    private bool $openInNewTab;

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


    /**
     * @return string
     */
    public function getPageType(): string
    {
        return $this->pageType;
    }

    /**
     * @return string
     */
    public function getFormattedPageType(): string
    {
        return ucfirst(str_replace('_', ' ', $this->pageType));
    }

    /**
     * @param string $pageType
     * @return $this
     */
    public function setPageType(string $pageType): WebPageLink
    {
        $this->pageType = $pageType;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasRequirements(): bool
    {
        return !empty($this->requirements);
    }

    /**
     * @return string
     */
    public function getRequirements(): string
    {
        return $this->requirements;
    }

    /**
     * @param string $requirements
     * @return $this
     */
    public function setRequirements(string $requirements): WebPageLink
    {
        $this->requirements = $requirements;
        return $this;
    }

    public function doIncludeInMenus(): bool
    {
        return !$this->excludeFromMenus;
    }

    /**
     * @return bool
     */
    public function doExcludeFromMenus(): bool
    {
        return $this->excludeFromMenus;
    }

    /**
     * @param bool $excludeFromMenus
     * @return $this
     */
    public function setExcludeFromMenus(bool $excludeFromMenus): WebPageLink
    {
        $this->excludeFromMenus = $excludeFromMenus;
        return $this;
    }

    /**
     * @return bool
     */
    public function doShowSubPageImages(): bool
    {
        return $this->showSubPageImages;
    }

    /**
     * @param bool $showSubPageImages
     * @return WebPageLink
     */
    public function setShowSubPageImages(bool $showSubPageImages): WebPageLink
    {
        $this->showSubPageImages = $showSubPageImages;
        return $this;
    }

    /**
     * @return bool
     */
    public function doOpenInNewTab(): bool
    {
        return $this->openInNewTab;
    }

    /**
     * @param bool $openInNewTab
     * @return WebPageLink
     */
    public function setOpenInNewTab(bool $openInNewTab): WebPageLink
    {
        $this->openInNewTab = $openInNewTab;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasBreadcrumb(): bool
    {
        return !empty($this->breadcrumb);
    }

    /**
     * @return string
     */
    public function getBreadcrumb(): string
    {
        return $this->breadcrumb;
    }

    /**
     * @param string $breadcrumb
     * @return WebPageLink
     */
    public function setBreadcrumb(string $breadcrumb): WebPageLink
    {
        $this->breadcrumb = $breadcrumb;
        return $this;
    }



    
}
