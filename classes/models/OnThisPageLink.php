<?php

namespace BSBI\WebBase\models;


/**
 * Class WebPageLink
 * Represents a simple web page link
 *
 * @package BSBI\Web
 */
class OnThisPageLink extends BaseModel
{


    /**
     * @var string
     */
    private string $anchorLink;

    private string $linkLevel;

    private string $linkArea;

    /**
     * @param string $anchorLink
     * @param string $linkLevel
     */
    public function __construct(string $linkTitle, string $anchorLink, string $linkLevel, string $linkArea)
    {
        $this->anchorLink = $anchorLink;
        $this->linkLevel = $linkLevel;
        $this->linkArea = $linkArea;
        parent::__construct($linkTitle, '');
    }

    /**
     * @return string
     */
    public function getAnchorLink(): string
    {
        return $this->anchorLink;
    }

    /**
     * @param string $anchorLink
     * @return OnThisPageLink
     */
    public function setAnchorLink(string $anchorLink): OnThisPageLink
    {
        $this->anchorLink = $anchorLink;
        return $this;
    }

    /**
     * @return string
     */
    public function getLinkLevel(): string
    {
        return $this->linkLevel;
    }

    /**
     * @param string $linkLevel
     * @return OnThisPageLink
     */
    public function setLinkLevel(string $linkLevel): OnThisPageLink
    {
        $this->linkLevel = $linkLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getLinkArea(): string
    {
        return $this->linkArea;
    }

    /**
     * @param string $linkArea
     * @return OnThisPageLink
     */
    public function setLinkArea(string $linkArea): OnThisPageLink
    {
        $this->linkArea = $linkArea;
        return $this;
    }

}
