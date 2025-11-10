<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\BaseWebPage;
use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;

/**
 *
 */
trait CoreWebPageProperties {

    protected string $pageId;

    /**
     * the page type (or template) for the page
     * @var string
     */
    protected string $pageType = '';

    protected string $description ='';

    /**
     * @var WebPageLinks
     */
    protected WebPageLinks $subPages;

    protected bool $isCookieConsentGiven = false;

    private string $cookieConsentCSRFToken = '';


    /**
     * @return string
     */
    public function getPageId(): string
    {
        return $this->pageId;
    }

    /**
     * @param string $pageId
     * @return CoreWebPageProperties|BaseWebPage|WebPageLink
     */
    public function setPageId(string $pageId): self
    {
        $this->pageId = $pageId;
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
     * @param string $pageType
     * @return static
     */
    public function setPageType(string $pageType): static
    {
        $this->pageType = $pageType;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return isset($this->description);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return CoreWebPageProperties|BaseWebPage|WebPageLink
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * does the page have any subpages?
     * @return bool
     */
    public function hasSubPages(): bool
    {
        return isset($this->subPages) && $this->subPages->hasListItems();
    }

    /**
     * @return WebPageLinks
     */
    public function getSubPages(): WebPageLinks
    {
        return $this->subPages;
    }

    /**
     * @param WebPageLinks $subPages
     * @return static
     */
    public function setSubPages(WebPageLinks $subPages): static {
        $this->subPages = $subPages;
        return $this;
    }

    /**
     * @param WebPageLink $subPage
     * @return static
     */
    public function addSubPage(WebPageLink $subPage): static
    {
        $this->subPages->addListItem($subPage);
        return $this;
    }

    /**
     * @return bool
     */
    public function isCookieConsentGiven(): bool
    {
        return $this->isCookieConsentGiven;
    }

    /**
     * @param bool $isCookieConsentGiven
     * @return static
     */
    public function setIsCookieConsentGiven(bool $isCookieConsentGiven): static
    {
        $this->isCookieConsentGiven = $isCookieConsentGiven;
        return $this;
    }

    /**
     * @return string
     */
    public function getCookieConsentCSRFToken(): string
    {
        return $this->cookieConsentCSRFToken;
    }

    /**
     * @param string $cookieConsentCSRFToken
     * @return static
     */
    public function setCookieConsentCSRFToken(string $cookieConsentCSRFToken): static
    {
        $this->cookieConsentCSRFToken = $cookieConsentCSRFToken;
        return $this;
    }


}