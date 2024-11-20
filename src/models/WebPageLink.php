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

    private Image $panelImage;

    /**
     * @var string
     */
    private string $panelDescription;

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
    public function hasPanelImage(): bool
    {
        return isset($this->panelImage);
    }

    /**
     * @return Image
     */
    public function getPanelImage(): Image
    {
        return $this->panelImage;
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function setPanelImage(Image $image): WebPageLink
    {
        $this->panelImage = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getPanelDescription(): string
    {
        return $this->panelDescription;
    }

    /**
     * @param string $panelDescription
     * @return $this
     */
    public function setPanelDescription(string $panelDescription): WebPageLink
    {
        $this->panelDescription = $panelDescription;
        return $this;
    }


}
