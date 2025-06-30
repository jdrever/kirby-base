<?php

namespace BSBI\WebBase\models;

use BSBI\Web\traits\TagProperties;
use BSBI\WebBase\traits\CoreWebPageProperties;

/**
 * Represents a web page
 *
 * @package BSBI\Web
 */
class BaseWebPage extends BaseModel
{
    use CoreWebPageProperties;

    /**
     * @var User
     */
    protected User $currentUser;

    /**
     * @var string[]
     */
    protected array $requiredUserRoles = [];

    /**
     * @var string
     */
    protected string $openGraphTitle = '';

    /**
     * @var string
     */
    protected string $openGraphDescription = '';

    /**
     * @var string
     */
    protected string $openGraphImage = '';

    /**
     * @var string
     */
    protected string $authors = '';

    /**
     * @var Languages
     */
    protected Languages $languages;

    /**
     * @var WebPageBlocks
     */
    protected WebPageBlocks $mainContentBlocks;

    /**
     * @var WebPageBlocks
     */
    protected WebPageBlocks $lowerContentBlocks;

    /**
     * @var WebPageLinks
     */
    protected WebPageLinks $menuPages;


    /**
     * @var WebPageLinks
     */
    protected WebPageLinks $breadcrumb;

    /**
     * @var CoreLinks
     */
    protected CoreLinks $coreLinks;

    /**
     * @var string
     */
    protected string $colourMode;

    /**
     * @var string
     */
    protected string $query = '';

    /**
     * @var RelatedContentList
     */
    protected RelatedContentList $relatedContent;

    /**
     * @var WebPageTagLinks
     */
    private WebPageTagLinks $tagLinks;



    /**
     * @var WebPageTagLinks
     */
    private WebPageTagLinks $taggedByLinks;

    /**
     * @var string[]
     */
    protected array $attributes = [];

    /**
     * the custom scripts for the page
     * @var string[]
     */
    protected array $customScripts;

    /**
     * @var bool
     */
    protected bool $usingSimpleLinksForSubPages = false;

    /**
     * @var string
     */
    protected string $urlWithQueryString = '';



    /**
     * @param string $title
     * @param string $url
     * @param string $pageType
     */
    public function __construct(string $title, string $url, string $pageType)
    {
        $this->pageType = $pageType;
        $this->mainContentBlocks = new WebPageBlocks();
        $this->menuPages = new WebPageLinks();
        $this->subPages = new WebPageLinks();
        $this->breadcrumb = new WebPageLinks();
        $this->coreLinks = new CoreLinks();
        $this->customScripts = [];
        $this->languages = new Languages();
        parent::__construct($title, $url);
    }


    /**
     * @return string
     */
    public function getOpenGraphTitle(): string
    {
        return $this->openGraphTitle;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setOpenGraphTitle(string $title): self
    {
        $this->openGraphTitle = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpenGraphDescription(): string
    {
        return $this->openGraphDescription;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setOpenGraphDescription(string $description): self
    {
        $this->openGraphDescription = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpenGraphImage(): string
    {
        return $this->openGraphImage;
    }

    /**
     * @param string $imageUrl
     * @return $this
     */
    public function setOpenGraphImage(string $imageUrl): self
    {
        $this->openGraphImage = $imageUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthors(): string
    {
        return $this->authors;
    }

    /**
     * @param string $authors
     * @return $this
     */
    public function setAuthors(string $authors): BaseWebPage
    {
        $this->authors = $authors;
        return $this;
    }



    /**
     * @return WebPageBlocks
     */
    public function getMainContent(): WebPageBlocks
    {
        return $this->mainContentBlocks;
    }

    /**
     * @param WebPageBlock $block
     * @return $this
     */
    public function addMainContentBlock(WebPageBlock $block): self
    {
        $this->mainContentBlocks->addListItem($block);
        return $this;
    }


    /**
     * @param WebPageBlocks $mainContentBlocks
     * @return $this
     */
    public function setMainContentBlocks(WebPageBlocks $mainContentBlocks): self
    {
        $this->mainContentBlocks = $mainContentBlocks;
        return $this;
    }

    /**
     * @return WebPageBlocks
     */
    public function getLowerContentBlocks(): WebPageBlocks
    {
        return $this->lowerContentBlocks;
    }

    /**
     * @param WebPageBlocks $lowerContentBlocks
     * @return void
     */
    public function setLowerContentBlocks(WebPageBlocks $lowerContentBlocks): void
    {
        $this->lowerContentBlocks = $lowerContentBlocks;
    }

    /**
     * @param string $blockType
     * @return bool
     */
    public function hasBlockofType(string $blockType): bool {
        return $this->mainContentBlocks->hasBlockOfType($blockType);
    }

    /**
     * @return bool
     */
    public function hasHeadingBlock(): bool {
        return $this->mainContentBlocks->hasBlockOfType('heading');
    }

    /**
     * @return bool
     */
    public function hasBreadcrumb(): bool
    {
        return (isset($this->breadcrumb) && $this->breadcrumb->hasListItems());
    }

    /**
     * @return WebPageLinks
     */
    public function getBreadcrumb(): WebPageLinks
    {
        return $this->breadcrumb;
    }

    /**
     * @param WebPageLinks $breadcrumb
     * @return $this
     */
    public function setBreadcrumb(WebPageLinks $breadcrumb): self
    {
        $this->breadcrumb = $breadcrumb;
        return $this;
    }

    /**
     * @return WebPageLinks
     */
    public function getMenuPages(): WebPageLinks
    {
        return $this->menuPages ?? new WebPageLinks();
    }

    /**
     * @param WebPageLinks $menuPages
     * @return $this
     */
    public function setMenuPages(WebPageLinks $menuPages): self
    {
        $this->menuPages = $menuPages;
        return $this;
    }

    /**
     * @return CoreLinks
     */
    public function getCoreLinks(): CoreLinks
    {
        return $this->coreLinks;
    }


    /**
     * @param CoreLink $coreLink
     * @return $this
     */
    public function addCoreLink(CoreLink $coreLink): self {
        $this->coreLinks->addListItem($coreLink);
        return $this;
    }

    /**
     * @param CoreLinks $coreLinks
     * @return $this
     */
    public function setCoreLinks(CoreLinks $coreLinks): self
    {
        $this->coreLinks = $coreLinks;
        return $this;
    }

    /**
     * @param RelatedContent $relatedContent
     * @return $this
     */
    public function addRelatedContent(RelatedContent $relatedContent): self
    {
        $this->relatedContent->addListItem($relatedContent);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasRelatedContent(): bool {
        return isset($this->relatedContent)&&$this->relatedContent->count()>0;
    }

    /**
     * @return $this
     */
    public function setRelatedContentList(RelatedContentList $relatedContentList): self {
        $this->relatedContent = $relatedContentList;
        return $this;
    }

    /**
     * @return RelatedContentList
     */
    public function getRelatedContentList(): RelatedContentList {
        return $this->relatedContent;
    }

    /**
     * @return bool
     */
    public function hasTags():bool {
        return false;
    }


    /**
     * @return bool
     */
    public function hasTaggedByLinks(): bool {

        if (!isset($this->taggedByLinks) || $this->taggedByLinks->count() === 0) {
            return false;
        }
        foreach ($this->taggedByLinks->getListItems() as $taggedByLink) {
            if ($taggedByLink->hasLinks()) { return true; }
        }
        return false;

    }

    /**
     * @return WebPageTagLinks
     */
    public function getTaggedByLinks(): WebPageTagLinks
    {
        return $this->taggedByLinks;
    }

    /**
     * @param WebPageTagLinks $taggedByLinks
     * @return $this
     */
    public function setTaggedByLinks(WebPageTagLinks $taggedByLinks): BaseWebPage
    {
        $this->taggedByLinks = $taggedByLinks;
        return $this;
    }



    /**
     * @return WebPageTagLinks
     */
    public function getTagLinks(): WebPageTagLinks
    {
        return $this->tagLinks;
    }

    /**
     * @param WebPageTagLinks $tagLinks
     * @return $this
     */
    public function setTagLinks(WebPageTagLinks $tagLinks): self
    {
        $this->tagLinks = $tagLinks;
        return $this;
    }

    /**
     * @return Languages
     */
    public function getLanguages(): Languages
    {
        return $this->languages;
    }

    /**
     * @param Languages $languages
     * @return $this
     */
    public function setLanguages(Languages $languages): self
    {
        $this->languages = $languages;
        return $this;
    }


    /**
     * @return string
     */
    public function getColourMode(): string
    {
        return $this->colourMode ?? 'light';
    }

    /**
     * @param string $colourMode
     * @return $this
     */
    public function setColourMode(string $colourMode): self
    {
        $this->colourMode = $colourMode;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasQuery(): bool
    {
        return !empty($this->query);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }


    /**
     * add a script to the page
     * @param string $script
     * @return self
     */
    public function addScript(string $script): self
    {
        if (!in_array($script, $this->customScripts)) {
            $this->customScripts[] = $script;
        }
        return $this;
    }

    /**
     * get all scripts for the page
     * @return string[]
     */
    public function getScripts(): array
    {
        return $this->customScripts;
    }

    /**
     * @return bool
     */
    public function hasCurrentUser(): bool {
        return isset($this->currentUser);
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    /**
     * @param User $user
     * @return self
     */
    public function setCurrentUser(User $user): self
    {
        $this->currentUser = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentUserRole(): string
    {
        return $this->currentUser->getRole();
    }

    /**
     * @return string[]
     */
    public function getRequiredUserRoles(): array
    {
        return $this->requiredUserRoles;
    }

    /**
     * @param string[] $requiredUserRoles
     * @return void
     */
    public function setRequiredUserRoles(array $requiredUserRoles): void
    {
        $this->requiredUserRoles = $requiredUserRoles;
    }

    /**
     * @return bool
     */
    public function checkUserAgainstRequiredRoles(): bool
    {
        return ($this->checkRoleAgainstRequiredRoles($this->currentUser->getRole()));
    }

    /**
     * @param string $role
     * @return bool
     */
    public function checkRoleAgainstRequiredRoles(string $role): bool
    {
        if (count($this->requiredUserRoles) === 0) {
            return true;
        }
        return (in_array($this->currentUser->getRole(), $this->requiredUserRoles, true));

    }

    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string[] $attributes
     * @return self
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttribute(string $attributeType): bool
    {
        return in_array($attributeType, $this->attributes, true);
    }

    /**
     * @return bool
     */
    public function isUsingSimpleLinksForSubPages(): bool
    {
        return $this->usingSimpleLinksForSubPages;
    }

    /**
     * @return string
     */
    public function getUrlWithQueryString(): string
    {
        return $this->urlWithQueryString;
    }

    /**
     * @param string $urlWithQueryString
     * @return $this
     */
    public function setUrlWithQueryString(string $urlWithQueryString): BaseWebPage
    {
        $this->urlWithQueryString = $urlWithQueryString;
        return $this;
    }


    /**
     * @param string $queryString
     * @return string
     */
    public function addQueryString(string $queryString): string {
        $separator = str_contains($this->urlWithQueryString, '?') ? '&' : '?';
        return $this->urlWithQueryString . $separator . $queryString;
    }

    /**
     * override to stop KirbyBaseHelper getting subPages
     * @return bool
     */
    public function doSimpleGetSubPages(): bool {
        return true;
    }

}