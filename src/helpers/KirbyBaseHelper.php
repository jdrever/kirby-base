<?php

namespace BSBI\WebBase\helpers;

use BSBI\WebBase\models\ActionStatus;
use BSBI\WebBase\models\BaseFilter;
use BSBI\WebBase\models\BaseList;
use BSBI\WebBase\models\BaseModel;
use BSBI\WebBase\models\BaseWebPage;
use BSBI\WebBase\models\CoreLink;
use BSBI\WebBase\models\Document;
use BSBI\WebBase\models\Documents;
use BSBI\WebBase\models\FeedbackForm;
use BSBI\WebBase\models\Image;
use BSBI\WebBase\models\ImageSizes;
use BSBI\WebBase\models\ImageType;
use BSBI\WebBase\models\Language;
use BSBI\WebBase\models\Languages;
use BSBI\WebBase\models\LoginDetails;
use BSBI\WebBase\models\Pagination;
use BSBI\WebBase\models\PrevNextPageNavigation;
use BSBI\WebBase\models\WebPageBlock;
use BSBI\WebBase\models\WebPageBlocks;
use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;
use BSBI\WebBase\models\User;
use BSBI\WebBase\models\WebPageTagLinks;
use BSBI\WebBase\models\WebPageTagLinkSet;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks;
use Kirby\Data\Yaml;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\Structure;
use Kirby\Cms\StructureObject;
use Kirby\Content\Field;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Cookie;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use Throwable;

/**
 *
 */
abstract class KirbyBaseHelper
{

    #region CONSTRUCTOR
    /**
     * The Kirby object
     * @var App
     */
    protected App $kirby;
    /**
     * The Kirby site object
     * @var Site
     */
    protected Site $site;
    /**
     * The Kirby page object
     * @var Page|null
     */
    protected ?Page $page;

    /**
     * Class constructor, passing in the Kirby objects
     * @param App $kirby the Kirby object
     * @param Site $site the Kirby site object
     * @param Page|null $page the Kirby page object
     * @throws Throwable
     */
    public function __construct(App $kirby, Site $site, ?Page $page)
    {
        $this->kirby = $kirby;
        $this->site = $site;
        $this->page = $page;
    }
    #endregion

    #region PAGES

    /**
     * @return BaseWebPage
     * @noinspection PhpUnused
     */
    abstract function getBasicPage(): BaseWebPage;

    /**
     * @param Page $kirbyPage
     * @param BaseWebPage $currentPage
     * @return BaseWebPage
     * @noinspection PhpUnused
     */
    abstract function setBasicPage(Page $kirbyPage, BaseWebPage $currentPage): BaseWebPage;

    /**
     * @param string $pageClass
     * @return BaseWebPage
     */
    public function getCurrentPage(string $pageClass = BaseWebPage::class) : BaseWebPage {
        return $this->getSpecificPage($this->page->id(), $pageClass);
    }

    /**
     * Retrieves a specific page by its ID and casts it to the specified page class type.
     * Optionally checks user roles during the process. Handles errors and allows for
     * additional processing via customisable methods.
     *
     * @param string $pageId The unique identifier of the page to be retrieved.
     * @param string $pageClass The class to which the page should be cast. Defaults to BaseWebPage.
     * @param bool $checkUserRoles Whether to validate user roles during the retrieval process. Defaults to true.
     * @return BaseWebPage The retrieved and processed page object.
     */
    public function getSpecificPage(string $pageId,
                                    string $pageClass = BaseWebPage::class,
                                    bool   $checkUserRoles = true) : BaseWebPage {
        try {
            $kirbyPage = $this->getKirbyPage($pageId);
            $page = $this->getPage($kirbyPage, $pageClass, $checkUserRoles);

            if (method_exists($this, 'setBasicPage')) {
                $page = $this->setBasicPage($kirbyPage, $page);
            }

            $setPageFunction = 'set'.$this->extractClassName($pageClass);

            if (method_exists($this, $setPageFunction)) {
                $page = $this->$setPageFunction($kirbyPage, $page);
            }

        } catch (KirbyRetrievalException $e) {
            $page = $this->recordPageError($e, $pageClass);
        }
        return $page;
    }


    /**
     * @param Page $page
     * @param string $pageClass the type of class to return (must extend WebPage)
     * @param bool $checkUserRoles
     * @return BaseWebPage
     */
    protected function getPage(Page   $page,
                               string $pageClass = BaseWebPage::class,
                               bool   $checkUserRoles = true): BaseWebPage
    {
        try {

            // Ensure $pageClass is a subclass of WebPage
            if (!(is_a($pageClass, BaseWebPage::class, true))) {
                throw new KirbyRetrievalException("Page class must extend BaseWebPage.");
            }

            $webPage = new $pageClass(
                $page->title()->toString(),
                $page->url(),
                $page->template()->name()
            );

            $webPage->setUrlWithQueryString($_SERVER['REQUEST_URI']);

            $user = $this->getCurrentUser();

            $webPage->setCurrentUser($user);

            if ($checkUserRoles) {
                if (!$this->checkPagePermissions($page)) {
                    $this->redirectToLogin($page->url());
                }
            }

            $webPage->setDescription(
                $this->isPageFieldNotEmpty($page, 'description')
                    ? $this->getPageFieldAsString($page, 'description')
                    : $this->getSiteFieldAsString('description')
            );

            $webPage->setBreadcrumb($this->getBreadcrumb());

            $webPage->setMenuPages($this->getMenuPages());

            if ($webPage->doSimpleGetSubPages()) {
                $webPage->setSubPages($this->getSubPages($page, $webPage->isUsingSimpleLinksForSubPages()));
            }
            if ($this->isPageFieldNotEmpty($page, 'mainContent')) {
                $webPage->setMainContentBlocks($this->getContentBlocks($page));
            }

            if ($this->isPageFieldNotEmpty($page, 'lowerContent')) {
                $webPage->setLowerContentBlocks($this->getContentBlocks($page, 'lowerContent'));
            }

            if ($this->isPageFieldNotEmpty($page, 'related')) {
                if ($this->getPageFieldType($page, 'related') === 'structure') {
                    $webPage->setRelatedContentList($this->getRelatedContentListFromStructureField(
                        $page,
                        'related',
                        ImageType::PANEL
                    ));
                }
                if ($this->getPageFieldType($page, 'related') === 'pages') {
                    $webPage->setRelatedContentList($this->getRelatedContentListFromPagesField($page));
                }
            }

            $webPage->setTagLinks($this->getTagLinks($page));

            $openGraphTitle = $this->isPageFieldNotEmpty($page, 'og_title')
                ? $this->getPageFieldAsString($page, 'og_title')
                : $page->title()->toString();
            $webPage->setOpenGraphTitle($openGraphTitle);

            $openGraphDescription = $this->isPageFieldNotEmpty($page, 'og_description')
                ? $this->getPageFieldAsString($page, 'og_description')
                : $this->getSiteFieldAsString('og_description');

            $webPage->setOpenGraphDescription($openGraphDescription);

            if ($this->isPageFieldNotEmpty($page, 'og_image')) {
                $ogImage = $this->getPageFieldAsFile($page, 'og_image');
                $webPage->setOpenGraphImage($ogImage->url());
            } else {
                $webPage->setOpenGraphDescription($this->site->url() . '/assets/images/BSBI-long-colour.svg');
            }

            $webPage->setColourMode($this->getColourMode());
            $webPage->setLanguages($this->getLanguages());

            //add scripts for blocks
            if ($webPage->hasBlockofType('video')) {
                $webPage->addScript('lite-youtube');
            }

            if ($actionStatus = get('actionStatus')) {
                $webPage->setStatus($actionStatus);
                $webPage->addFriendlyMessage(get('friendlyMessage', 'Unknown status'));
            }

            $query = $this->getSearchQuery();

            if (!empty($query)) {
                $webPage->setQuery($query);
                $webPage = $this->highlightSearchQuery($webPage, $query);
            }
        } catch (KirbyRetrievalException $e) {
            $webPage = $this->recordPageError($e, $pageClass);
        }

        return $webPage;
    }

    /**
     * @param Page $kirbyPage
     * @param string $pageClass
     * @return BaseWebPage
     */
    protected function getEmptyWebPage(Page $kirbyPage, string $pageClass = BaseWebPage::class): BaseWebPage
    {
        $webPage = new $pageClass($kirbyPage->title()->toString(), $kirbyPage->url(), $kirbyPage->template()->name());
        $webPage->setPageId($kirbyPage->id());
        $user = $this->getCurrentUser();
        $webPage->setCurrentUser($user);
        return $webPage;
    }

    /**
     * @param string $title
     * @return BaseWebPage
     * @noinspection PhpUnused
     */
    protected function findPage(string $title): BaseWebPage
    {
        $page = $this->findKirbyPage($title);
        if ($page !== null) {
            $webPage = new BaseWebPage($page->title()->toString(), $page->url(), $page->template()->name());
        } else {
            $webPage = new BaseWebPage('', '', '');
            $webPage->setStatus(false);
        }
        return $webPage;
    }

    /**
     * @param string $pageId
     * @return Page
     * @throws KirbyRetrievalException
     */
    protected function getKirbyPage(string $pageId): Page
    {
        $page = $this->kirby->page($pageId);
        if (!$page instanceof Page) {
            throw new KirbyRetrievalException('Page not found');
        }
        return $page;
    }

    /**
     * @param string $title
     * @param Page|null $parentPage
     * @return Page|null
     */
    protected function findKirbyPage(string $title, ?Page $parentPage = null): Page|null
    {
        $page = $parentPage === null
            ? $this->site->children()->find($title)
            : $parentPage->children()->find($title);
        if ($page !== null) {
            if ($page instanceof Page) {
                return $page;
            }
            if ($page instanceof Collection) {
                if ($page->first() instanceof Page) {
                    return $page->first();
                }
            }
        }
        return null;
    }

    /**
     * @param string $collectionName
     * @return Collection
     * @throws KirbyRetrievalException
     */
    protected function getPagesFromCollection(string $collectionName): Collection
    {
        $pages = $this->kirby->collection($collectionName);
        if (!isset($pages)) {
            throw new KirbyRetrievalException('Collection ' . $collectionName . ' pages not found');
        }
        return $pages;
    }

    /**
     * @param string $collectionName
     * @return array
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageTitlesFromCollection(string $collectionName): array
    {
        $pages = $this->getPagesFromCollection($collectionName);
        $fields = $pages->pluck('title');

        // Check if the result is an array and if its first element is a Field object
        if (count($fields) > 0 && $fields[0] instanceof Field) {
            // If it is, map the array to convert each Field object to a string
            return array_map(function ($field) {
                return (string) $field;
            }, $fields);
        }

        // Otherwise, return the result directly (it should already be a string array)
        return $fields;
    }

    /**
     * @param string $pageId
     * @return string
     * @noinspection PhpUnused
     */
    public function getParentPageName(string $pageId): string
    {
        $page = $this->kirby->page($pageId);
        if ($page instanceof Page) {
            $parentPage = $page->parent();
            if ($parentPage instanceof Page) {
                return $parentPage->title()->toString();
            }
        }
        return '';
    }

    #endregion

    #region PAGE_FIELDS

    /**
     * Gets the field - if the field is empty, returns an empty string
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException if the page or field cannot be found
     * @noinspection PhpUnused
     */
    public function getCurrentPageField(string $fieldName): string
    {
        if (!isset($this->page)) {
            throw new KirbyRetrievalException('Current page not found');
        }
        return $this->getPageFieldAsString($this->page, $fieldName);
    }

    /**
     * Gets the field - if the field is empty, returns an empty string
     * @return string
     * @throws KirbyRetrievalException if the page or field cannot be found
     * @noinspection PhpUnused
     */
    public function getCurrentPageTitle(): string
    {
        if (!isset($this->page)) {
            throw new KirbyRetrievalException('Current page not found');
        }
        return $this->page->title()->toString();
    }

    /**
     * Gets the field - if the field is empty, returns an empty string
     */
    public function getPageTitle($page): string
    {
        return $page->title()->toString();
    }

    /**
     * Gets the field - if the field is empty, returns an empty string
     * @param $page
     * @return string
     * @noinspection PhpUnused
     */
    public function getPageUrl($page): string
    {
        return $page->url();
    }

    /**
     * Gets the field - if the field is empty, returns an empty string
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @param string $defaultValue
     * @return string
     * @throws KirbyRetrievalException if the page or field cannot be found
     */
    protected function getPageFieldAsString(Page   $page,
                                            string $fieldName,
                                            bool   $required = false,
                                            string $defaultValue = ''): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            return $pageField->toString();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return $defaultValue;
        }
    }

    /**
     * Retrieves the specified field from the page as a string. If the field is empty or an exception occurs,
     * then return the provided fallback value.
     *
     * @param Page $page The page object containing the field.
     * @param string $fieldName The name of the field to retrieve.
     * @param string $fallback The fallback value to return if the field is empty or an error occurs.
     * @return string The field value as a string, or the fallback value if the field is empty
     * or an exception is caught.
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsStringWithFallback(Page $page, string $fieldName, string $fallback): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            return $pageField->isNotEmpty() ? $pageField->toString() : $fallback;
        } catch (KirbyRetrievalException) {
            return $fallback;
        }
    }



    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return int
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsInt(Page $page, string $fieldName, bool $required = false): int
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toInt();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            //TODO: should be better than returning zero if not required.  Maybe return int|null
            return 0;

        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return float
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsFloat(Page $page, string $fieldName, bool $required = false): float
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toFloat();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            //TODO: should be better than returning zero if not required.  Maybe return float|null
            return 0;
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @param bool $default
     * @return bool
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsBool(Page   $page,
                                          string $fieldName,
                                          bool   $required = false,
                                          bool   $default = false): bool
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toBool();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return $default;
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsYesNo(Page $page, string $fieldName, bool $required = false): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return ($pageField->toBool() === true) ? 'Yes' : 'No';
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            //TODO: should be better than returning false if not required.  Maybe return bool|null
            return 'NO';
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return ?DateTime
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsDateTime(Page $page, string $fieldName, bool $required = false): ?DateTime
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return (new DateTime())->setTimestamp($pageField->toDate());
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return null;
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return DateTime
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsRequiredDateTime(Page $page, string $fieldName): DateTime
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return (new DateTime())->setTimestamp($pageField->toDate());
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $isRequired
     * @return Structure
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsStructure(Page $page, string $fieldName, bool $isRequired = false): Structure
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toStructure();
        }
        catch (KirbyRetrievalException $e) {
            if ($isRequired) {
                throw $e;
            }
            return new Structure();
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsUrl(Page $page, string $fieldName, bool $required = false): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            //TODO: temporary fix here for PHP 8.1, adding null check
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toUrl() ?? '';
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @param int $excerpt
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsBlocksHtml(Page   $page,
                                                string $fieldName,
                                                bool   $required = false,
                                                int    $excerpt = 0): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            $blockContent = $pageField->toBlocks()->toHtml();

            return ($excerpt === 0)
                ? $blockContent
                : Str::excerpt($blockContent, 200);

        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return Blocks
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsBlocks(Page $page, string $fieldName): Blocks
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $pageField->toBlocks();
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return string[]
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsArray(Page $page, string $fieldName, bool $required = false): array
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->split();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return [];
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsKirbyText(Page $page, string $fieldName, bool $required = false): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->kti()->toString();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return File|null
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsFile(Page $page, string $fieldName): File|null
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $pageField->toFile();
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return File|null
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsFiles(Page $page, string $fieldName): Files|null
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $pageField->toFiles();
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $simpleLinks
     * @return WebPageLink
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsWebPageLink(Page $page, string $fieldName, bool $simpleLinks = true): WebPageLink
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            $page = $pageField->toPage();
            return $this->getWebPageLink($page, $simpleLinks);
        } catch (KirbyRetrievalException $e) {
            return (new WebPageLink('','','','error'))->recordError('Page not found: '.$e->getMessage());
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $simpleLinks
     * @return WebPageLinks
     */
    protected function getPageFieldAsWebPageLinks(Page $page, string $fieldName, bool $simpleLinks = true): WebPageLinks
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            $pages = $pageField->toPages();
            return $this->getWebPageLinks($pages, $simpleLinks);
        } catch (KirbyRetrievalException) {
            return new WebPageLinks();
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $isRequired
     * @return Pages|null
     * @throws KirbyRetrievalException
     */
    protected function getPageFieldAsPages(Page $page, string $fieldName, bool $isRequired = false): Pages|null
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toPages();
        } catch (KirbyRetrievalException) {
            if ($isRequired) {
                throw new KirbyRetrievalException('The field ' . $fieldName . ' does not exist');
            }
            return null;
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsPageTitle(Page $page, string $fieldName): string {
        $pages = $this->getPageFieldAsPages($page, $fieldName);
        $page = $pages->first();
        if ($page) {
            return $page->title()->toString();
        }
        return '';
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsPageUrl(Page $page, string $fieldName): string {
        $pages = $this->getPageFieldAsPages($page, $fieldName);
        $page = $pages->first();
        if ($page) {
            return $page->url();
        }
        return '';
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param string $title
     * @return Document
     */
    protected function getPageFieldAsDocument(Page $page, string $fieldName, string $title = 'Download'): Document
    {
        try {
            $pageFile = $this->getPageFieldAsFile($page, $fieldName);
            if ($pageFile != null) {
                return $this->getDocumentFromFile($pageFile, $title);
            }
            return (new Document(''))->recordError('Document not found');
        } catch (KirbyRetrievalException) {
            return (new Document(''))->recordError('Document not found');
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param string $title
     * @return Documents
     * @noinspection PhpUnused
     */
    protected function getPageFieldAsDocuments(Page $page, string $fieldName, string $title = 'Download'): Documents
    {
        $documents = new Documents();
        try {
            $pageFiles = $this->getPageFieldAsFiles($page, $fieldName);
            if ($pageFiles != null) {
                foreach($pageFiles as $pageFile) {
                    $documents->addListItem($this->getDocumentFromFile($pageFile, $title));
                }
                return $documents;
            }
            return (new Documents())->recordError('Documents not found');
        } catch (KirbyRetrievalException) {
            return (new Documents())->recordError('Documents not found');
        }
    }

    /**
     * @param $pageFile
     * @param string $title
     * @return Document
     */
    private function getDocumentFromFile($pageFile, string $title='Download'): Document {
        $url = $pageFile->url();
        /** @noinspection PhpUndefinedMethodInspection */
        $size = $pageFile->niceSize();
        $document = new Document($title, $url);
        $document->setSize($size);
        return $document;
    }

    /**
     * @param File $file
     * @return DateTime
     * @noinspection PhpUnused
     */
    protected function getFileModifiedAsDateTime(File $file): DateTime {
        $modified = $file->modified();
        return (new DateTime())->setTimestamp($modified);
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return bool
     */
    protected function isPageFieldNotEmpty(Page $page, string $fieldName): bool
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->isNotEmpty();
        } catch (KirbyRetrievalException) {
            return false;
        }
    }


    /**
     * @param Page $page
     * @param string $fieldName
     * @return Field
     * @throws KirbyRetrievalException
     */
    protected function getPageField(Page $page, string $fieldName): Field
    {
        try {
            $pageField = $page->content()->get($fieldName);

            if (!$pageField instanceof Field) {
                throw new KirbyRetrievalException('The field ' . $fieldName . ' does not exist');
            }
            if ($pageField->isEmpty()) {
                throw new KirbyRetrievalException('The field ' . $fieldName . ' is empty');
            }
            return $pageField;
        } catch (InvalidArgumentException) {
            throw new KirbyRetrievalException('The field ' . $fieldName . ' does not exist');
        }
    }

    /**
     * Retrieves the type of the specified field from the page's content.
     * @param Page $page The page from which the field's type is to be retrieved.
     * @param string $fieldName The name of the field whose type is to be retrieved.
     * @return string The type of the field as a string.
     * @throws KirbyRetrievalException if the field does not exist.
     */
    protected function getPageFieldType(Page $page, string $fieldName): string
    {
        try {
            $blueprintFields = $page->blueprint()->fields();
            if (isset($blueprintFields[$fieldName])) {
                return $blueprintFields[$fieldName]['type'];
            } else {
                throw new InvalidArgumentException(
                    'The field "' . $fieldName . '" is not defined in the blueprint for page "' . $page->id() . '".'
                );
            }
        } catch (InvalidArgumentException $e) {
            // Catch the InvalidArgumentException thrown internally or by our check
            throw new KirbyRetrievalException($e->getMessage(), 0, $e);
        }
    }

    #endregion

    #region SITE_FIELDS

    /**
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getSiteFieldAsString(string $fieldName, bool $required = false): string
    {
        try {
            $siteField = $this->getSiteField($fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $siteField->toString();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param string $fieldName
     * @param bool $required
     * @param bool $default
     * @return bool
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getSiteFieldAsBool(string $fieldName, bool $required = false, bool $default = false): bool
    {
        try {
            $siteField = $this->getSiteField($fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $siteField->toBool();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return $default;
        }
    }

    /**
     * @param string $fieldName
     * @return Structure
     * @throws KirbyRetrievalException
     */
    protected function getSiteFieldAsStructure(string $fieldName): Structure
    {
        $siteField = $this->getSiteField($fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $siteField->toStructure();
    }

    /**
     * @param string $fieldName
     * @return Page
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getSiteFieldAsPage(string $fieldName): Page
    {
        $siteField = $this->getSiteField($fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $siteField->toPage();
    }

    /**
     * @param string $fieldName
     * @return Field
     * @throws KirbyRetrievalException
     */
    protected function getSiteField(string $fieldName): Field
    {
        try {
            $siteField = $this->site->content()->get($fieldName);
            if (!$siteField instanceof Field) {
                throw new KirbyRetrievalException('Site field not found');
            }
            if ($siteField->isEmpty()) {
                throw new KirbyRetrievalException('Site field is empty');
            }
            return $siteField;
        } catch (InvalidArgumentException) {
            throw new KirbyRetrievalException('Site field not found');
        }
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    protected function isSiteFieldNotEmpty(string $fieldName): bool {
        try {
            return $this->site->content()->get($fieldName)->isNotEmpty();
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    #endregion

    #region STRUCTURE_FIELDS

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getStructureFieldAsString(StructureObject $structure,
                                                 string          $fieldName,
                                                 bool            $required = false): string
    {
        try {
            $structureField = $this->getStructureField($structure, $fieldName);
            return $structureField->toString();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            } else {
                return '';
            }

        }
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @param bool $required
     * @param bool $default
     * @return bool
     * @throws KirbyRetrievalException
     */
    protected function getStructureFieldAsBool(StructureObject $structure,
                                               string          $fieldName,
                                               bool            $required = false,
                                               bool            $default = false): bool
    {
        try {
            $structureField = $this->getStructureField($structure, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $structureField->toBool();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return $default;
        }
    }

    /**
     * NOTE: will return 0 if required and field not found/empty
     * @param StructureObject $structure
     * @param string $fieldName
     * @param bool $required
     * @param int $default
     * @return int
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsInt(StructureObject $structure,
                                              string          $fieldName,
                                              bool            $required = false,
                                              int             $default = 0): int
    {
        try {
            $structureField = $this->getStructureField($structure, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $structureField->toInt();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            //TODO: should be better than returning zero if not required.  Maybe return int|null
            return $default;
        }
    }

    /**
     *  NOTE: will return 0 if required and field not found/empty
     * @param StructureObject $structure
     * @param string $fieldName
     * @param bool $required
     * @param float $default
     * @return float
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsFloat(StructureObject $structure,
                                                string          $fieldName,
                                                bool            $required = false,
                                                float           $default = 0): float
    {
        try {
            $structureField = $this->getStructureField($structure, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $structureField->toFloat();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return $default;
        }

    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsKirbyText(StructureObject $structure,
                                                    string          $fieldName,
                                                    bool            $required = false): string
    {
        try {
            $structureField = $this->getStructureField($structure, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $structureField->kti()->toString();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }

    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getStructureFieldAsLinkUrl(StructureObject $structure, string $fieldName): string
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        if ($structureField->isNotEmpty()) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $structureField->toUrl();
        } else {
            return '';
        }
    }


    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return Page
     * @throws KirbyRetrievalException
     */
    protected function getStructureFieldAsPage(StructureObject $structure, string $fieldName): Page
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        $page = $structureField->toPage();
        if ($page) {
            return $page;
        }
        else {
            throw new KirbyRetrievalException('The page field ' . $fieldName . ' does not exist');
        }
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return File
     * @throws KirbyRetrievalException
     */
    protected function getStructureFieldAsFile(StructureObject $structure, string $fieldName): File
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        $file = $structureField->toFile();
        if ($file) {
            return $file;
        }
        else {
            throw new KirbyRetrievalException('The file field ' . $fieldName . ' does not exist');
        }
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsPageTitle(StructureObject $structure, string $fieldName): string
    {
        $page = $this->getStructureFieldAsPage($structure, $fieldName);
        return $page->title()->value();
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsPageUrl(StructureObject $structure, string $fieldName): string
    {
        $page = $this->getStructureFieldAsPage($structure, $fieldName);
        return $page->url();
    }

    /**
     * Retrieves a field from a structure object and converts it to a URL if possible.
     * If the field's link object is empty, an empty string is returned.
     *
     * @param StructureObject $structure The structure object containing the field.
     * @param string $fieldName The name of the field to retrieve.
     * @return WebPageLink
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsWebPageLink(StructureObject $structure, string $fieldName): WebPageLink
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        $linkPage = $structureField->toPage();
        if ($linkPage->isNotEmpty()) {
            return new WebPageLink($linkPage->title()->value(),
                $linkPage->url(),
                $linkPage->id(),
                $linkPage->template()->name());
        }
        $webPageLink =  new WebPageLink('Not found', '', '', '');
        $webPageLink->setStatus(false);
        return $webPageLink;
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param ImageType $imageType (optional, if image is found)
     * @return WebPageLinks
     * @noinspection PhpUnused
     */
    protected function getStructureAsWebPageLinks(Page      $page,
                                                  string    $fieldName = 'related',
                                                  ImageType $imageType = ImageType::SQUARE) : WebPageLinks
    {
        $webPageLinks = new WebPageLinks();
        try {
            $webPageLinksStructure = $this->getPageFieldAsStructure($page, $fieldName);

            foreach ($webPageLinksStructure as $item) {
                $itemTitle = $this->getStructureFieldAsString($item, 'title');
                if ($this->hasStructureField($item, 'page')) {
                    $page = $this->getStructureFieldAsPage($item, 'page');
                    $itemTitle = empty($itemTitle) ? $page->title()->value() : $itemTitle;
                    $description = $this->getStructureFieldAsString($item, 'description');
                    $webPageLink = $this->getWebPageLink($page, true, $itemTitle, $description);
                    if ($this->hasStructureField($item, 'image')) {
                        $image = $this->getImageFromStructureField(
                           $item,
                            'image',
                            400,
                            300,
                            80,
                            $imageType,
                            '',
                            ImageSizes::HALF_LARGE_SCREEN
                        );
                        $webPageLink->setImage($image);
                    }
                    $webPageLinks->addListItem($webPageLink);
                } else {
                    if (!empty($itemTitle)) {
                        $webPageLink = new WebPageLink(
                            strval($item->title()),
                            $this->getStructureFieldAsLinkUrl($item, 'url'),
                            '',
                            ''
                        );
                        $webPageLinks->addListItem($webPageLink);
                    }
                }
            }
        }
        catch (KirbyRetrievalException $e) {
            $webPageLinks->setStatus(false);
            $webPageLinks->addErrorMessage($e->getMessage());
            $webPageLinks->addFriendlyMessage('No web page links found');
        }
        return $webPageLinks;
    }

    /**
     * @param StructureObject $structureObject
     * @param string $fieldName
     * @param bool $required
     * @param int $excerpt
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getStructureFieldAsBlocksHtml(StructureObject $structureObject,
                                                     string          $fieldName,
                                                     bool            $required = false,
                                                     int             $excerpt = 0): string
    {
        try {
            $structureField = $this->getStructureField($structureObject, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            $blockContent = $structureField->toBlocks()->toHtml();

            return ($excerpt === 0)
                ? $blockContent
                : Str::excerpt($blockContent, 200);

        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * If structure has title field, will use that for the document title
     * @param Page $page
     * @param string $fieldName
     * @return Documents
     * @noinspection PhpUnused
     */
    protected function getStructureAsDocuments(Page $page, string $fieldName) : Documents
    {
        $documents = new Documents();
        try {
            $documentsStructure = $this->getPageFieldAsStructure($page, $fieldName);

            foreach ($documentsStructure as $item) {
                $docTitle = $this->getStructureFieldAsString($item, 'title');
                if ($this->hasStructureField($item, 'upload')) {
                    $file = $this->getStructureFieldAsFile($item, 'upload');
                    /** @noinspection PhpUndefinedMethodInspection */
                    $docTitle = empty($docTitle) ? $file->filename() : $docTitle;
                    $document = new Document($docTitle, $file->url());
                    $documents->addListItem($document);
                }
            }
        }
        catch (KirbyRetrievalException $e) {
            $documents->setStatus(false);
            $documents->addErrorMessage($e->getMessage());
            $documents->addFriendlyMessage('No documents found');
        }
        return $documents;
    }



    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return Field
     * @throws KirbyRetrievalException
     */
    protected function getStructureField(StructureObject $structure, string $fieldName): Field
    {
        //TODO: sort out this code to get the value back
        $structureField = $structure->content()->get($fieldName);
        if (!$structureField instanceof Field || $structureField->isEmpty()) {
            throw new KirbyRetrievalException('Structure field not found or empty');
        }
        return $structureField;
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return bool
     */
    protected function hasStructureField(StructureObject $structure, string $fieldName): bool
    {
        $structureField = $structure->content()->get($fieldName);
        return ($structureField->isNotEmpty() && $structureField instanceof Field);
    }

    #endregion

    #region ENTRIES_FIELDS

    /**
     * @param Page $page
     * @param string $fieldName
     * @return string []
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getEntriesFieldAsStringArray(Page $page, string $fieldName): array {
        $field= $this->getPageField($page, $fieldName);
        $fieldAsArray = [];
        /** @noinspection PhpUndefinedMethodInspection */
        foreach($field->toEntries() as $entry) {
            $fieldAsArray[] = $entry->toString();
        }
        return $fieldAsArray;
    }

    #endregion

    #region BLOCK_FIELDS

    /**
     * @param Block $block
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getBlockFieldAsString(Block $block, string $fieldName, bool $required = false): string
    {
        try {
            $blockField = $this->getBlockField($block, $fieldName);
            return $blockField->toString();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @param bool $required
     * @return int
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getBlockFieldAsInt(Block $block, string $fieldName, bool $required = false): int
    {
        try {
            $blockField = $this->getBlockField($block, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $blockField->toInt();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            //TODO: do better than returning zero
            return 0;
        }
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @param int $width
     * @param int|null $height
     * @param ImageType $imageType
     * @param bool $fixedWidth
     * @return Image
     * @throws InvalidArgumentException
     * @noinspection PhpUnused
     */
    protected function getBlockFieldAsImage(Block     $block,
                                            string    $fieldName,
                                            int       $width,
                                            ?int      $height,
                                            ImageType $imageType = ImageType::SQUARE,
                                            bool      $fixedWidth = false): Image
    {
        try {
            $blockImage = $this->getBlockFieldAsFile($block, $fieldName);
            //var_dump($blockImage);
            if ($blockImage != null) {
                if ($fixedWidth) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    if ($blockImage->width() < $width) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $width = $blockImage->width();
                    }
                    $blockImage = $blockImage->resize($width);
                    $src = $blockImage->url();
                    $srcSet = $blockImage->srcset([
                        '1x' => ['width' => $width],
                        '2x' => ['width' => $width * 2],
                        '3x' => ['width' => $width * 3],
                    ]);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $height = $blockImage->height();
                    $webpSrcSet = $blockImage->srcset('webp');
                } else {
                    $srcSetType = ($imageType === ImageType::SQUARE) ? 'square' : 'main';
                    $src = $blockImage->crop($width, $height)->url();
                    $srcSet = $blockImage->srcset($srcSetType);
                    $webpSrcSet = $blockImage->srcset($srcSetType . '-webp');
                }
                /** @noinspection PhpUndefinedMethodInspection */
                $alt = $blockImage->alt()->isNotEmpty() ? $blockImage->alt()->value() : '';
                if ($src !== null && $srcSet !== null && $webpSrcSet !== null) {
                    return new Image ($src, $srcSet, $webpSrcSet, $alt, $width, $height);
                }
            }
            return (new Image())->recordError('Image not found');
        } catch (KirbyRetrievalException) {
            return (new Image())->recordError('Image not found');
        }
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getBlockFieldAsBlocks(Block $block, string $fieldName, bool $required = false): string
    {
        try {
            $blockField = $this->getBlockField($block, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $blockField->toBlocks();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getBlockFieldAsBlocksHtml(Block $block, string $fieldName, bool $required = false): string
    {
        try {
            $blockField = $this->getBlockField($block, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $blockField->toBlocks()->toHtml();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            return '';
        }
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @return bool
     */
    protected function isBlockFieldNotEmpty(Block $block, string $fieldName): bool
    {
        try {
            $blockField = $this->getBlockField($block, $fieldName);
            return $blockField->isNotEmpty();
        } catch (KirbyRetrievalException) {
            return false;
        }
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @return Field
     * @throws KirbyRetrievalException
     */
    protected function getBlockField(Block $block, string $fieldName): Field
    {
        $blockField = $block->content()->get($fieldName);
        if (!$blockField instanceof Field || $blockField->isEmpty()) {
            throw new KirbyRetrievalException('Block field not found or empty');
        }
        return $blockField;
    }

    /**
     * @param Block $block
     * @param string $fieldName
     * @return File|null
     * @throws KirbyRetrievalException
     */
    protected function getBlockFieldAsFile(Block $block, string $fieldName): File|null
    {
        $blockField = $this->getBlockField($block, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $blockField->toFile();
    }

    #endregion



    #region MENU PAGES/NAVIGATION


    /**
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    protected function getMenuPages(): WebPageLinks
    {
        /** @var Collection|null $menuPagesCollection */
        $menuPagesCollection = $this->kirby->collection('menuPages');
        if (isset($menuPagesCollection)) {
            return $this->getWebPageLinks($menuPagesCollection);
        } else {
            return (new WebPageLinks())->recordError('No menu pages found');
        }
    }

    /**
     * @param Page $page
     * @param bool $simpleLink
     * @param array|null $templates
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    protected function getSubPages(Page $page, bool $simpleLink = true, array $templates = null ): WebPageLinks
    {
        $subPagesCollection = $templates ?
            $this->getSubPagesUsingTemplates($page, $templates) : $this->getSubPagesAsCollection($page);
        if ($subPagesCollection instanceof Collection) {
            return $this->getWebPageLinks($subPagesCollection, $simpleLink);
        }
        return new WebPageLinks();
    }


    /**
     * get the pages below the current page (excluding certain template types)
     * if home page, return menu pages without the home page itself
     * @param Page $page
     * @return Pages|Collection|null
     */
    protected function getSubPagesAsCollection(Page $page): Pages|Collection|null
    {
        if ($page->template()->name() !== 'home') {
            $excludedTemplates = option('subPagesExclude');

            // Ensure it returns an array
            if (!is_array($excludedTemplates)) {
                $excludedTemplates = [];
            }
            return $page->children()->listed()->notTemplate($excludedTemplates);
        }
        $menuPages = $this->kirby->collection('menuPages');
        if ($menuPages instanceof Collection) {
            return $menuPages->filterBy('template', '!=', 'home');
        }
        return null;
    }

    /**
     * @param Page $page
     * @param string $template
     * @return WebPageLink
     * @noinspection PhpUnused
     */
    protected function getSubPageLink(Page $page, string $template): WebPageLink
    {
        $reportPagesFromKirby = $this->getSubPagesUsingTemplates($page, [$template]);
        if ($reportPagesFromKirby->count() > 0) {
            $reportPageFromKirby = $reportPagesFromKirby->first();
            if ($reportPageFromKirby instanceof Page) {
                return new WebPageLink(
                    $reportPageFromKirby->title()->toString(),
                    $reportPageFromKirby->url(),
                    $reportPageFromKirby->id(),
                    $reportPageFromKirby->template()->name()
                );
            }
        }
        return (new WebPageLink('', '', '', 'error'))->recordError('Page not found');
    }

    /**
     * get the pages below the current page
     * @param Page $page
     * @param string[] $templates
     * @param bool $childrenOnly
     * @return Pages
     */
    protected function getSubPagesUsingTemplates(Page $page, array $templates, bool $childrenOnly = true): Pages
    {
        return $childrenOnly
            ? $page->children()->listed()->template($templates)
            : $page->index()->listed()->template($templates);
    }

    /**
     * @param Page $page
     * @param array $templates
     * @return Page|null
     * @noinspection PhpUnused
     */
    protected function getFirstSubPageUsingTemplates(Page $page, array $templates): Page|null {
        return $this->getSubPagesUsingTemplates($page, $templates)->first();
    }

    /**
     * @param Page $page
     * @param array $templates
     * @noinspection PhpUnused
     * @return Pages
     */
    protected function getSiblingsUsingTemplates(Page $page, array $templates): Pages {
        /** @var Pages $siblings */
        $siblings = $page->siblings(false);
        return $siblings->filterBy('template', 'in', $templates)->listed();
    }

    /**
     * Generates previous and next page navigation details for a given page within a specified collection.
     *
     * @param Page $kirbyPage The current page for which the navigation is being generated.
     * @param string $collectionName The name of the collection from which the navigation should be computed.
     * @return PrevNextPageNavigation Object containing previous and next page navigation data.
     * @noinspection PhpUnused
     */
    protected function getPrevNextNavigation(Page $kirbyPage, string $collectionName) : PrevNextPageNavigation {
        $navigation = new PrevNextPageNavigation();
        $contentCollection = $this->kirby->collection($collectionName, ['page' => $kirbyPage]);
        $previousPage = $this->page->prev($contentCollection);
        $nextPage = $this->page->next($contentCollection);
        if ($previousPage) {
            $navigation->setPreviousPageLink($previousPage->url());
            $navigation->setPreviousPageTitle($previousPage->title()->value());
        }
        if ($nextPage) {
            $navigation->setNextPageLink($nextPage->url());
            $navigation->setNextPageTitle($nextPage->title()->value());
        }
        return $navigation;
    }


    #endregion

    #region LINKS

    /**
     * @param string $title
     * @param string $linkType
     * @return CoreLink
     * @noinspection PhpUnused
     */
    protected function findCoreLink(string $title, string $linkType): CoreLink
    {
        $page = $this->site->children()->find($title);
        if ($page instanceof Page) {
            $coreLink = new CoreLink($page->title()->toString(), $page->url(), $linkType);
        } else {
            $coreLink = new CoreLink('', '', 'NOT_FOUND');
            $coreLink->setStatus(false);
        }
        return $coreLink;
    }

    /**
     * @param Page $page
     * @param string $linkType
     * @return CoreLink
     * @noinspection PhpUnused
     */
    protected function getCoreLink(Page $page, string $linkType): CoreLink
    {
        return new CoreLink($page->title()->toString(), $page->url(), $linkType);
    }



    /**
     * @param Collection $collection
     * @param bool $simpleLink
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    protected function getWebPageLinks(Collection $collection, bool $simpleLink = true): WebPageLinks
    {
        $webPageLinks = new WebPageLinks();
        /** @var Page $collectionPage */
        foreach ($collection as $collectionPage) {
            $webPageLinks->addListItem($this->getWebPageLink($collectionPage, $simpleLink));
        }
        return $webPageLinks;
    }


    /**
     * @param Page $page
     * @param bool $simpleLink
     * @param string|null $linkTitle
     * @param string|null $linkDescription
     * @return WebPageLink
     * @throws KirbyRetrievalException
     */
    protected function getWebPageLink(Page   $page,
                                      bool   $simpleLink = true,
                                      string $linkTitle = null,
                                      string $linkDescription = null): WebPageLink
    {
        $templateName = $page->template()->name();
        if ($templateName === 'file_link') {
            $file = $this->getPageFieldAsFile($page, 'file');
            $pageUrl = $file ? $file->url() : '';
        } else {
            $pageUrl = $page->url();
        }

        $linkDescription = $linkDescription ?? $this->getPageFieldAsString($page, 'panelContent');
        $linkTitle = $linkTitle ?? $this->getPageTitle($page);
        $webPageLink = new WebPageLink($linkTitle, $pageUrl , $page->id(), $page->template()->name());
        $webPageLink->setLinkDescription($linkDescription);
        if ($this->isPageFieldNotEmpty($page, 'requirements')) {
            $webPageLink->setRequirements($this->getPageFieldAsString($page, 'requirements'));
        }
        if ($simpleLink) {
            return $webPageLink;
        }
        if ($this->isPageFieldNotEmpty($page, 'panelImage')) {
            $panelImage = $this->getImage($page, 'panelImage', 400, 300, 80, ImageType::PANEL);
            $panelImage->setClass('img-fix-size img-fix-size--four-three');
            $webPageLink->setImage($panelImage);
        }

        $webPageLink->setShowSubPageImages($this->getPageFieldAsBool($page, 'showSubPageImages'));
        $webPageLink->setSubPages($this->getSubPages($page, $simpleLink));
        return $webPageLink;
    }




    #endregion

    #region MODELS
    /**
     * @template T of BaseModel
     * @param string $pageId
     * @param class-string<T> $modelClass
     * @return T
     */
    public function getSpecificModel(string $pageId, string $modelClass) : mixed {
        try {
            $kirbyPage = $this->getKirbyPage($pageId);
            if (!(is_a($modelClass, BaseModel::class, true))) {
                throw new KirbyRetrievalException("Page class must extend BaseModel.");
            }

            $model = new $modelClass($kirbyPage->title()->toString(), $kirbyPage->url());

            $setModelFunction = 'set'.$this->extractClassName($modelClass);

            if (method_exists($this, $setModelFunction)) {
                $model = $this->$setModelFunction($kirbyPage, $model);
            }
        } catch (KirbyRetrievalException $e) {
            $model = $this->recordModelError($e, $modelClass);
        }
        return $model;
    }

    /**
     * @param string $modelListClass
     * @param BaseFilter|null $filter
     * @param string|null $collection
     * @param array|null $templates
     * @param Page|null $parentPage
     * @param bool $childrenOnly
     * @param string $sortBy (default is no sorting applied)
     * @param string $sortDirection
     * @return BaseList
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getSpecificModelList(string          $modelListClass = BaseList::class,
                                          BaseFilter|null $filter = null,
                                          string|null     $collection = null,
                                          array|null      $templates = null,
                                          Page $parentPage = null,
                                          bool $childrenOnly = true,
                                          string $sortBy = '',
                                          string $sortDirection = ''
    ): BaseList
    {

        // Ensure $pageClass is a subclass of WebPage
        if (!(is_a($modelListClass, BaseList::class, true))) {
            throw new KirbyRetrievalException("Model list class must extend BaseList.");
        }

        $modelList = new $modelListClass();


        $modelClassName = $modelList->getItemType();
        $modelClass = $modelClassName;
        // Ensure $modelClass is a subclass of BaseModel
        if (!(is_a($modelClass, BaseModel::class, true))) {
            throw new KirbyRetrievalException("Model class must extend BaseModel.");
        }

        $filterClassName = $this->extractClassName($modelList->getFilterType());
        $filterClass = $filterClassName;

        if (isset($templates)) {
            $parentPage = $parentPage ?? $this->page;
            $collectionPages = $this->getSubPagesUsingTemplates($parentPage, $templates, $childrenOnly);
        }
        else {
            if ($collection === null) {
                $collection = str_replace("List", "", $this->extractClassName($modelListClass));
                $collection = lcfirst($collection);
            }
            $collectionPages = $this->kirby->collection($collection);
            if (!isset($collectionPages)) {
                throw new KirbyRetrievalException('Collection ' . $collection . ' pages not found');
            }
        }

        if ($filter === null) {
            $setFilterFunction = 'set' . $this->extractClassName($filterClass);
            if (method_exists($this, $setFilterFunction)) {
                $filter = $this->$setFilterFunction();
            }
        }
        if ($filter) {
            $modelList->setFilters($filter);
            $filterFunction = 'filter' . $this->extractClassName($modelListClass);

            if (method_exists($this, $filterFunction)) {
                $collectionPages = $this->$filterFunction($collectionPages, $filter);
            }
        }


        if (!empty($sortBy)) {
            $collectionPages = $collectionPages->sortBy($sortBy, $sortDirection);
        }

        if ($modelList->usePagination()) {

            $collectionPages = $collectionPages->paginate($modelList->getPaginatePerPage());
            $paginationFromKirby = $collectionPages->pagination();

            if (isset($paginationFromKirby)) {
                $modelList->setPagination($this->getPagination($paginationFromKirby));
            }
        }

        /** @var Page $collectionPage */
        foreach ($collectionPages as $collectionPage) {
            $model = $this->getSpecificModel($collectionPage, $modelClass);
            $modelList->addListItem($model);
        }

        return $modelList;
    }
    #endregion

    #region IMAGES

    /**
     * @param Page $page
     * @param string $fieldName
     * @param int $width
     * @param int $height
     * @param int $quality
     * @param ImageType $imageType
     * @param string $imageFormat
     * @param ImageSizes $imageSizes
     * @return Image
     * @throws KirbyRetrievalException
     */
    protected function getImage(Page       $page,
                                string     $fieldName,
                                int        $width,
                                int        $height,
                                int        $quality = 90,
                                ImageType  $imageType = ImageType::SQUARE,
                                string     $imageFormat = '',
                                ImageSizes $imageSizes = ImageSizes::NOT_SPECIFIED) : Image {

        $pageImage = $this->getPageFieldAsFile($page, $fieldName);
        return $this->getImageFromFile($pageImage, $width, $height, $quality, $imageType, $imageFormat, $imageSizes);
    }

    /**
     * @param StructureObject $structureObject
     * @param string $fieldName
     * @param int $width
     * @param int $height
     * @param int $quality
     * @param ImageType $imageType
     * @param string $imageFormat
     * @param ImageSizes $imageSizes
     * @return Image
     * @throws KirbyRetrievalException
     */
    protected function getImageFromStructureField(StructureObject $structureObject,
                                                  string          $fieldName,
                                                  int             $width,
                                                  int             $height,
                                                  int             $quality = 90,
                                                  ImageType       $imageType = ImageType::SQUARE,
                                                  string          $imageFormat = '',
                                                  ImageSizes      $imageSizes = ImageSizes::NOT_SPECIFIED) : Image {

        $structureImage = $this->getStructureFieldAsFile($structureObject, $fieldName);
        return $this->getImageFromFile($structureImage,
            $width,
            $height,
            $quality,
            $imageType,
            $imageFormat,
            $imageSizes);
    }


    /**
     * @param File $image
     * @param int $width
     * @param ?int $height
     * @param int $quality
     * @param ImageType $imageType
     * @param string $imageFormat (e.g. webp)
     * @param ImageSizes $imageSizes
     * @return Image
     * @throws KirbyRetrievalException
     */
    protected function getImageFromFile(File       $image,
                                        int        $width,
                                        ?int       $height,
                                        int        $quality = 90,
                                        ImageType  $imageType = ImageType::SQUARE,
                                        string     $imageFormat = '',
                                        ImageSizes $imageSizes = ImageSizes::NOT_SPECIFIED): Image
    {
        $thumbOptions = [
            'width' => $width,
            'height' => $height,
            'quality' => $quality,
            'crop' => true // Enable cropping
        ];

        // If a specific image format is provided, add it to the options.
        // This will instruct Kirby to generate a thumbnail in that format.
        if (!empty($imageFormat)) {
            $thumbOptions['format'] = $imageFormat;
        }

        try {
            $src = $image->thumb($thumbOptions)->url();
        }
        catch (InvalidArgumentException $e) {
            throw new KirbyRetrievalException('The image could not be retrieved: ' . $e->getMessage());
        }
        $srcSetType = strtolower($imageType->value);
        $srcSet = $image->srcset($srcSetType);
        $webpSrcSet = $image->srcset($srcSetType . '-webp');
        /** @noinspection PhpUndefinedMethodInspection */
        $alt = $image->alt()->isNotEmpty() ? $image->alt()->value() : '';
        if ($src !== null && $srcSet !== null && $webpSrcSet !== null) {
            return (new Image ($src, $srcSet, $webpSrcSet, $alt, $width, $height))->setSizes($imageSizes->value);
        }
        return (new Image())->recordError('Image not found');

    }
    #endregion


    #region RELATED CONTENT

    /**
     * Retrieves a related content list from a structure field of the provided page.
     * Iterates through the structure field items, extracting and converting relevant data
     * to create a list of related content.
     *
     * @param Page $page The page object containing the structure field.
     * @param string $fieldName The name of the structure field to retrieve data from.
     * @return WebPageLinks The assembled list of related content items.
     * @throws KirbyRetrievalException
     */
    protected function getRelatedContentListFromPagesField(Page $page, string $fieldName = 'related') : WebPageLinks
    {
        $relatedContent = $this->getPageFieldAsPages($page, $fieldName);
        $relatedContentList = new WebPageLinks();
        foreach ($relatedContent as $item) {
            $itemTitle = $this->getPageTitle($item);
            if (!empty($itemTitle)) {
                $content = new WebPageLink(
                    $itemTitle,
                    $item->url(),
                    $item->id(),
                    $item->template()->name()
                );
                $relatedContentList->addListItem($content);
            }
        }
        return $relatedContentList;
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param ImageType $imageType
     * @return WebPageLinks
     */
    protected function getRelatedContentListFromStructureField(Page      $page,
                                                               string    $fieldName = 'related',
                                                               ImageType $imageType = ImageType::FIXED) : WebPageLinks
    {
        $webPageLinks = new WebPageLinks();
        try {
            $relatedLinksStructure = $this->getPageFieldAsStructure($page, $fieldName);

            foreach ($relatedLinksStructure as $item) {
                $itemTitle = $this->getStructureFieldAsString($item, 'title');
                if ($this->hasStructureField($item, 'url')) {
                    $page = $this->getStructureFieldAsPage($item, 'url');
                    $itemTitle = empty($itemTitle) ? $page->title()->value() : $itemTitle;
                    $description = $this->getStructureFieldAsString($item, 'description');
                    $webPageLink = $this->getWebPageLink($page, true, $itemTitle, $description);
                    $openInNewTab = $this->getStructureFieldAsBool($item, 'openInNewTab');
                    $webPageLink->setOpenInNewTab($openInNewTab);
                    if ($this->isPageFieldNotEmpty($page, 'panelImage')) {
                        $image = $this->getImage($page,
                            'panelImage',
                            400,
                            300,
                            80,
                            $imageType,
                            '',
                            ImageSizes::HALF_LARGE_SCREEN);
                        $webPageLink->setImage($image);
                    }
                    $webPageLinks->addListItem($webPageLink);
                } else {
                    if (!empty($itemTitle)) {
                        $webPageLink = new WebPageLink(
                            strval($item->title()),
                            $this->getStructureFieldAsLinkUrl($item, 'url'),
                            '',
                            ''
                        );
                        $webPageLinks->addListItem($webPageLink);
                    }
                }
            }
        }
        catch (KirbyRetrievalException $e) {
            $webPageLinks->setStatus(false);
            $webPageLinks->addErrorMessage($e->getMessage());
            $webPageLinks->addFriendlyMessage('No web page links found');
        }
        return $webPageLinks;
    }



    #endregion

    #region REQUESTS

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    protected function hasPostRequest(): bool
    {
        return ($this->kirby->request()->is('POST'));
    }

    /**
     * Checks if a request exists for the given key.
     *
     * @param string $key The key to check in the request.
     * @return bool Returns true if the request with the given key exists, false otherwise.
     * @noinspection PhpUnused
     */
    protected function hasRequest(string $key): bool {
        return get($key) !== null;
    }

    /**
     * @param string $key
     * @param string $fallback
     * @return string
     * @noinspection PhpUnused
     */
    protected function getRequestAsString(string $key, string $fallback = ''): string {
        return $this->asString(get($key),$fallback);
    }

    /**
     * Retrieves a request parameter or cookie value by the given key,
     * returning a fallback value if neither is available.
     *
     * @param string $key The key to look for in request parameters or cookies.
     * @param string $fallBack The fallback value to return if the key does not exist in both request and cookie data.
     * @return string The value retrieved from the request parameter, cookie, or the provided fallback.
     * @noinspection PhpUnused
     */
    protected function getRequestOrCookie(string $key, string $fallBack): string {
        return get($key) ?: cookie::get($key) ?: $fallBack;
    }


    #endregion

    #region ERROR HANDLING

    /**
     * @param $logFile .log is added
     * @param $message
     * @return void
     */
    protected function writeToLog($logFile, $message): void {
        $logDir = kirby()->root('logs');

        // Check if the directory doesn't exist and create it if necessary
        if (!is_dir($logDir)) {
            // The third parameter 'true' allows the creation of nested directories
            mkdir($logDir, 0755, true);
        }

        // Define the log file path
        $logFile = $logDir .'/'. $logFile.'.log';
        // Clear the log file at the start
        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * @param KirbyRetrievalException $e
     * @param string $friendlyMessage
     * @return ActionStatus
     * @noinspection PhpUnused
     */
    protected function actionStatusError(KirbyRetrievalException $e, string $friendlyMessage): ActionStatus
    {
        return (new ActionStatus(false, $e->getMessage(), $friendlyMessage, $e));
    }

    /**
     * @param KirbyRetrievalException $e
     * @param class-string<BaseWebPage> $pageClass
     * @return BaseWebPage
     */
    protected function recordPageError(KirbyRetrievalException $e, string $pageClass = BaseWebPage::class): BaseWebPage
    {
        $webPage = $this->getEmptyWebPage($this->page, $pageClass);
        $webPage
            ->recordError(
                $e->getMessage(),
                'An error occurred while retrieving the ' . $webPage->getTitle() . ' page.',
            );
        $this->sendErrorEmail($e);
        return $webPage;
    }

    /**
     * @param ActionStatus $actionStatus
     * @param class-string<BaseWebPage> $pageClass
     * @return BaseWebPage
     * @noinspection PhpUnused
     */
    protected function recordActionStatusError(ActionStatus $actionStatus,
                                               string       $pageClass = BaseWebPage::class): BaseWebPage
    {
        $webPage = $this->getErrorPage($pageClass);
        $webPage
            ->recordError(
                $actionStatus->getFirstErrorMessage(),
                'An error occurred while retrieving the ' . $webPage->getTitle() . ' page.',
            );
        $this->sendErrorEmail($actionStatus->getException());
        return $webPage;
    }


    /**
     * @param string $pageClass
     * @return BaseWebPage
     */
    protected function getErrorPage(string $pageClass = BaseWebPage::class) : BaseWebPage {
        /** @var BaseWebPage $webPage */
        $pageName = $this->extractClassName($pageClass);
        $webPage = new $pageClass($pageName, '', 'error');
        $user = $this->getCurrentUser();
        $webPage->setCurrentUser($user);
        return $webPage;
    }

    /**
     * @param KirbyRetrievalException $e
     * @param class-string<BaseModel> $modelClass
     * @return BaseModel
     */
    protected function recordModelError(KirbyRetrievalException $e, string $modelClass = BaseModel::class): BaseModel
    {
        /** @var BaseModel $model */
        $model = new $modelClass('Error', '', 'error');
        $modelName = str_replace('BSBI\\models\\', '', $modelClass);
        $model
            ->recordError(
                $e->getMessage(),
                'An error occurred while retrieving the ' . $modelName . '.',
            );
        $this->sendErrorEmail($e);
        return $model;
    }

    /**
     * @param string $template
     * @param string $from
     * @param string $replyTo
     * @param string $to
     * @param string $subject
     * @param array $data
     * @return void
     */
    protected function sendEmail(string $template,
                               string $from,
                               string $replyTo,
                               string $to,
                               string $subject,
                               array  $data) : void {
        try {
            if (!str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
                $this->kirby->email([
                    'template' => $template,
                    'from' => $from,
                    'replyTo' => $replyTo,
                    'to' => $to,
                    'subject' => $subject,
                    'data' => $data
                ]);
            }
        } catch (Throwable $error) {
            $this->writeToLog('errors', $error->getMessage());
        }
    }

    /**
     * @param KirbyRetrievalException $e
     * @return void
     */
    protected function sendErrorEmail(KirbyRetrievalException $e): void
    {

        $exceptionAsString =  "Message: " . $e->getMessage() . "\n" .
            "File:" . $e->getFile() . "'\n" .
            "Line:" . $e->getLine() . "\n" .
            "Trace:" . $e->getTraceAsString();
        $this->writeToLog('errors', $exceptionAsString);
        $this->sendEmail('error-notification',
            option('defaultEmail'),
            option('defaultEmail'),
            option('defaultEmail'),
            'Website Error',
            [
                'errorMessage' => $this->getExceptionDetails($e),
            ]
        );

    }

    /**
     * @param KirbyRetrievalException $exception
     * @return string
     */
    protected function getExceptionDetails(KirbyRetrievalException $exception): string
    {
        $details = "An exception was thrown in your application:<br><br>";
        $details .= $this->getExceptionDetail($exception);

        $userLoggedIn = $this->kirby->user();
        $userId = ($userLoggedIn) ? $userLoggedIn->id() : '';

        // Capture previous exceptions if they exist
        $previous = $exception->getPrevious();
        while ($previous) {
            $details .= "<b>Previous Exception:</b><br>";
            $details .= $this->getExceptionDetail($previous);
            $previous = $previous->getPrevious();
        }
        $details .= "<b>Request Details: </b>\n";
        $details .= "<b>URL: </b>" . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '') . "<br>";
        $details .= "<b>Method: </b>" . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . "<br>";
        $details .= "<b>IP Address: </b>" . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') . "<br>";
        $details .= "<b>User Agent: </b>" . ($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN') . "<br><br>";
        $details .= "<b>Kirby User ID: </b>" . $userId . "<br><br>";

        return $details;
    }

    /**
     * @param Throwable $exception
     * @return string
     */
    private function getExceptionDetail(Throwable $exception): string
    {
        $detail = "<b>Message:</b> " . $exception->getMessage() . "<br>";
        $detail .= "<b>Code:</b> " . $exception->getCode() . "<br>";
        $detail .= "<b>File:</b> " . $exception->getFile() . "<br>";
        $detail .= "<b>Line:</b> " . $exception->getLine() . "<br><br>";
        $detail .= "<b>Stack Trace</b> :\n" . $exception->getTraceAsString() . "<br><br>";
        return $detail;
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return WebPageBlocks
     */
    protected function getContentBlocks(Page $page, string $fieldName = 'mainContent'): WebPageBlocks
    {
        try {
            $pageBlocks = $this->getPageFieldAsBlocks($page, $fieldName);
            $contentBlocks = new WebPageBlocks();
            $headingNumber = 0;
            foreach ($pageBlocks as $pageBlock) {
                if ($pageBlock instanceof Block) {
                    $block = new WebPageBlock($pageBlock->type(), $pageBlock->toHtml());
                    if ($pageBlock->type() === 'heading') {
                        $block->setBlockLevel($pageBlock->content()->get('level')->toString());
                        $headingNumber++;
                        $anchor = ($this->isBlockFieldNotEmpty($pageBlock, 'anchor'))
                            ? $this->getBlockFieldAsString($pageBlock, 'anchor')
                            : 'heading' . $headingNumber;
                        $block->setAnchor($anchor);
                    }
                    $contentBlocks->addListItem($block);
                }
            }
            return $contentBlocks;
        } catch (KirbyRetrievalException $e) {
            return (new WebPageBlocks())->recordError(
                $e->getMessage(),
                'An error occurred while retrieving the page block'
            );
        }
    }



    #endregion

    #region BREADCRUMB

    /**
     * @return bool
     */
    protected function hasBreadcrumb(): bool
    {
        return (isset($this->page)
            && !$this->page->isHomePage()
            && (!($this->page->template()->name() === 'search'))
            && ($this->page->depth() > 1));
    }

    /**
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    protected function getBreadcrumb(): WebPageLinks
    {
        if (!isset($this->page) || !$this->hasBreadcrumb()) {
            return new WebPageLinks();
        }
        $breadcrumbPagesCollection = $this->getBreadcrumbAsCollection();
        return $this->getWebPageLinks($breadcrumbPagesCollection);
    }

    /**
     * get breadcrumb, filtering out home page
     * @return Collection
     */
    private function getBreadcrumbAsCollection(): Collection
    {
        return $this->site->breadcrumb()
            ->filterBy('template', '!=', 'home')
            ->filterBy('isListed', true)->not($this->page);
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return string
     * @noinspection PhpUnused
     */
    protected function getUserNames(Page $page, string $fieldName): string
    {
        try {
            // Get the 'postedBy' field from the current page
            $postedByField = $page->content()->get($fieldName);

            // Check if the field is not empty
            if ($postedByField instanceof Field && $postedByField->isNotEmpty()) :
                // Get the users from the 'postedBy' field
                /** @noinspection PhpUndefinedMethodInspection */
                $users = $postedByField->toUsers();

                // Initialise an array to hold usernames
                $userNamesArray = [];

                // Loop through the users to get their names
                foreach ($users as $user) :
                    $userNamesArray[] = $user->name();
                endforeach;

                // Convert the array of usernames to a string separated by commas
                $userNames = implode(', ', $userNamesArray);
            else :
                $userNames = 'Unknown';
            endif;
            return $userNames;
        } catch (\Exception) {
            return '';
        }
    }

    #endregion

    #region SEARCH

    /**
     * Get the query string (q)
     * @return string
     */
    protected function getSearchQuery(): string
    {
        $query = get('q', '');
        if (!is_string($query) || empty($query)) {
            $query = '';
        }
        return $query;
    }

    /**
     * @param string $query
     * @return WebPageLinks
     * @noinspection PhpUnused
     */
    protected function search(string $query): WebPageLinks
    {
        return $this->getSearchResults($query);
    }

    /**
     * if $specialSearchType is supplied, the function will look for a getWebPageLinksFor{$specialSearchType} function
     * if not, or if not matching function is provided, it will use getWebPageLinks
     * @param string $query
     * @param Collection|null $collection
     * @param string $specialSearchType
     * @return WebPageLinks
     */
    protected function getSearchResults(string      $query,
                                        ?Collection $collection = null,
                                        string      $specialSearchType = ''): WebPageLinks
    {
        $searchResults = new WebPageLinks();
        if (!empty($query)) {
            try {
                if ($collection === null) {
                    $collection = $this->getSearchCollection($query);
                }
                $doingDefaultSearch = true;
                if (!empty($specialSearchType)) {
                    $getWebPageLinksFunction = 'getWebPageLinksFor' . $specialSearchType;
                    if (method_exists($this, $getWebPageLinksFunction)) {
                        $searchResults = $this->$getWebPageLinksFunction($collection);
                        $doingDefaultSearch = false;
                    }
                }
                if ($doingDefaultSearch)  {
                    $searchResults = $this->getWebPageLinks($collection);
                }
                foreach ($searchResults->getListItems() as $searchResult) {
                    $highlightedTitle = $this->highlightTerm($searchResult->getTitle(), $query);
                    $highlightedDescription = $this->highlightTerm($searchResult->getDescription(), $query);
                    $searchResult->setTitle($highlightedTitle);
                    $searchResult->setDescription($highlightedDescription);
                }

                $paginationFromKirby = $collection->pagination();

                if (isset($paginationFromKirby)) {
                    $searchResults->setPagination($this->getPagination($paginationFromKirby));
                }
            } catch (\Exception $e) {
                $searchResults->recordError($e->getMessage(), 'An error occurred while retrieving the search results');
            }
        }

        return $searchResults;
    }

    /**
     * @param \Kirby\Cms\Pagination|\Kirby\Toolkit\Pagination $paginationFromKirby
     * @return Pagination
     */
    private function getPagination(\Kirby\Toolkit\Pagination|\Kirby\Cms\Pagination $paginationFromKirby): Pagination
    {
        $pagination = new Pagination();
        $pagination->setHasPreviousPage($paginationFromKirby->hasPrevPage());
        $pagination->setPreviousPageUrl($paginationFromKirby->prevPageURL() ?? '');
        $pagination->setHasNextPage($paginationFromKirby->hasNextPage());
        $pagination->setNextPageUrl($paginationFromKirby->nextPageURL() ?? '');
        $pagination->setCurrentPage($paginationFromKirby->page());
        $pagination->setPageCount($paginationFromKirby->pages());

        for ($i = 1; $i <= $paginationFromKirby->pages(); $i++) {
            $pagination->addPageUrl($paginationFromKirby->pageUrl($i) ?? '');
        }
        return $pagination;
    }

    /**
     * Search the specified Kirby collection
     * @param string|null $query
     * @param string $params
     * @param int $perPage the number of records per field
     * @param Collection|null $collection
     * @return Collection
     */
    protected function getSearchCollection(
        ?string     $query = null,
        string     $params = 'title|mainContent|description|keywords',
        int        $perPage = 10,
        ?Collection $collection = null
    ): Collection
    {
        if ($collection === null) {
            //TODO: may need multiple versions of this, depending on user permissions
            $collection = $this->site->index();
        }

        // returns an empty collection if there is no search query
        if (empty(trim($query ?? '')) === true) {
            return $collection->limit(0);
        }
        $params = ['fields' => Str::split($params, '|')];

        $defaults = [
            'fields' => [],
            'score' => []
        ];

        $options = array_merge($defaults, $params);
        $collection = clone $collection;

        $scores = [];
        $results = $collection->filter(function ($item) use ($query, $options, &$scores) {

            $data = $item->content()->toArray();
            //echo('<p><b>'.$data['title'].'</b></p>');
            $keys = array_keys($data);
            $keys[] = 'id';
            // apply the default score for pages
            $options['score'] = array_merge([
                'id' => 64,
                'title' => 64,
                'description' => 64,
                'keywords' => 64,
                'maincontent' => 32
            ], $options['score']);


            if (empty($options['fields']) === false) {
                $fields = array_map('strtolower', $options['fields']);
                $keys = array_intersect($keys, $fields);
            }


            $scoring = [
                'hits' => 0,
                'score' => 0
            ];
            $words = preg_split('/\s+/', $query); // Split the query into words

            if ($words === false) {
                $words = []; // fallback to an empty array if preg_split fails
            }
            //var_dump($options);

            foreach ($keys as $key) {
                $score = $options['score'][$key] ?? 1;
                $value = (string)$item->$key();
                //var_dump($data[$key]);

                // check for exact query matches
                if ($matches = preg_match_all('!' . preg_quote($query) . '!i', $value, $r)) {
                    $scoring['score'] += 10 * $matches * $score;
                    $scoring['hits'] += $matches;
                    //echo ('<p>' . $key . ' : exact match - score: '. (10*$matches *$score).'</p>');
                }

                $allWords = true;
                $wordMatches = 0;
                foreach ($words as $word) {
                    // Use preg_quote to escape any special characters in the word for regex
                    $word = preg_quote($word, '/');
                    // Create a regex pattern to match the word
                    $pattern = "/\b" . $word . "\b/i"; // \b is a word boundary, and 'i' is for case-insensitive

                    if ($matches = preg_match_all($pattern, $value, $r)) {
                        $wordMatches += $matches;
                        //echo ('<p>' . $key . ':' . $word . ' : match - score:'.$matches * $score.'</p>');
                    } else {
                        $allWords = false;
                    }
                }
                if ($allWords) {
                    //echo('<p>all words bonus</p>');
                    $scoring['score'] += 5 * $wordMatches * $score;
                } else {
                    $scoring['score'] += $wordMatches * $score;
                }
                $scoring['hits'] += $wordMatches;
            }
            //var_dump($scoring);

            $scores[$item->id()] = $scoring;
            return $scoring['hits'] > 0;
        });

        return $results->sort(
            fn($item) => $scores[$item->id()]['score'],
            'desc'
        )->paginate($perPage);
    }

    /**
     * Adds span class="highlight" to the term
     * @param string $text
     * @param string $term
     * @return string
     */
    private function highlightTerm(string $text, string $term): string
    {
        $term = preg_quote($term, '/');
        return preg_replace("/($term)/i", '<span class="highlight">$1</span>', $text) ?? $text;
    }

    /**
     * @param BaseWebPage $page
     * @param string $query
     * @return BaseWebPage
     */
    protected function highlightSearchQuery(BaseWebPage $page, string $query): BaseWebPage
    {
        $mainContentBlocks = $page->getMainContent();
        foreach ($mainContentBlocks->getListItems() as $block) {
            if (in_array($block->getBlockType(), ['text', 'heading', 'list', 'note'])) {
                $highlightedContent = $this->highlightTerm($block->getBlockContent(), $query);
                $block->setBlockContent($highlightedContent);
            }
        }
        return $page;
    }


    #endregion


    #region FEEDBACK


    /**
     * @return FeedbackForm
     * @throws KirbyRetrievalException
     * @noinspection PhpUnused
     */
    protected function getFeedbackForm(): FeedbackForm
    {
        $feedbackForm = new FeedbackForm();
        $feedbackForm->setTurnstileSiteKey($this->getTurnstileSiteKey());
        if ($this->kirby->request()->is('POST') && get('submit')) {

            $this->getTurnstileResponse();

            $name = get('name', '');
            $email = get('email', '');
            $feedback = get('feedback', '');
            $fromPage = get('page', '');

            $data = [
                'name' => is_string($name) ? $name : '',
                'email' => is_string($email) ? $email : '',
                'feedback' => is_string($feedback) ? $feedback : '',
                'feedbackPage' => is_string($fromPage) ? $fromPage : '',
            ];

            $rules = [
                'name' => ['required', 'minLength' => 3],
                'email' => ['required', 'email'],
                'feedback' => ['required', 'minLength' => 3, 'maxLength' => 3000],
            ];

            $messages = [
                'name' => 'Please enter a valid name',
                'email' => 'Please enter a valid email address',
                'feedback' => 'Please enter a text between 3 and 3000 characters'
            ];

            if ($invalid = invalid($data, $rules, $messages)) {
                $feedbackForm
                    ->setNameValue($data['name'])
                    ->setNameAlert($invalid['name'] ?? '')
                    ->setEmailValue($data['email'])
                    ->setEmailAlert($invalid['email'] ?? '')
                    ->setFeedbackValue($data['feedback'])
                    ->setFeedbackAlert($invalid['feedback'] ?? '')
                    ->setStatus(false)
                    ->addFriendlyMessage(
                        'The form was not sent.  Please review the messages below.'
                    );
            } else {
                $emailData = [
                    'text' => esc($data['feedback']),
                    'sender' => esc($data['name']),
                    'feedbackPage' => esc($data['feedbackPage'])
                ];

                $this->sendEmail('email',
                    'james.drever@bsbi-web.org',
                    $data['email'],
                    'james.drever@bsbi-web.org',
                    esc($data['name']) . ' sent you feedback from the BSBI website',
                    $emailData
                );
                $feedbackForm
                    ->addFriendlyMessage(
                    'Your feedback has been sent, thank you.'
                    )
                    ->setStatus(true);
            }
        }
        return $feedbackForm;
    }
    #endregion

    #region USERS

    /**
     * @param string $userId
     * @param string $fallback
     * @noinspection PhpUnused
     * @return string
     */
    protected function getUserName(string $userId, string $fallback = 'User not found') : string {
        // Extract the actual user ID from the string
        if (preg_match('/user:\/\/([a-zA-Z0-9]+)/', $userId, $matches)) {
            $userId = $matches[1]; // Get the ID (e.g., 'dvw7nX3C')

            // Check if the user exists
            $user = kirby()->user($userId);
            if ($user) {
                return $user->username();
            } else {
                return $fallback;
            }
        } else {
            return "User id is malformed";
        }
    }

    /**
     * @return User
     */
    protected function getCurrentUser(): User {
        $user = new User('user');

        $userLoggedIn = $this->kirby->user();
        $userId = ($userLoggedIn) ? $userLoggedIn->id() : '';
        $userName = ($userLoggedIn) ? $userLoggedIn->userName() : '';
        $role = ($userLoggedIn) ? $userLoggedIn->role()->name() : '';
        $user
            ->setUserId($userId)
            ->setUserName($userName)
            ->setRole($role);
        return $user;
    }

    /**
     * @param string $fieldName
     * @return string
     * @noinspection PhpUnused
     */
    public function getCurrentUserFieldAsString(string $fieldName): string {
        return $this->kirby->user()->{$fieldName}()->toString() ?? '';
    }

    /**
     * gets the current Kirby username - returns blank string if no user
     * @return string
     * @noinspection PhpUnused
     */
    protected function getCurrentUserName(): string {
        return $this->kirby->user() ? $this->kirby->user()->name() : '';
    }

    /**
     * gets the current Kirby username - returns blank string if no user
     * @return string
     * @noinspection PhpUnused
     */
    protected function getCurrentUserRole(): string {
        return $this->kirby->user() ? $this->kirby->user()->role()->name() : '';
    }


    /**
     * Checks whether the current user has the necessary permissions to access the given page.
     *
     * This function evaluates the user's role against the required roles defined in
     * the current page or inherited from its parent pages. It grants access to users
     * with 'admin' or 'editor' roles by default. If no roles are explicitly defined,
     * access is allowed.
     *
     * @param Page $currentPage The page for which permission is being checked
     * @return bool Returns true if the user has permission to access the page, false otherwise
     * @throws KirbyRetrievalException
     */
    protected function checkPagePermissions(Page $currentPage) : bool {

        if ($currentPage->template()->name() === 'login') { return true; }

        $user = $this->kirby->user();

        if ($user && !$user->isKirby() && ($user->role()->name() === 'admin' || $user->role()->name() === 'editor')) {
            return true;
        }
        $siteRoles = $this->getSiteFieldAsString('requiredRoles');

        if (!empty($siteRoles)) {
            $requiredRolesWithSpaces = explode(",",$siteRoles);
            $requiredRoles = array_map('trim', $requiredRolesWithSpaces);
            //if the site has roles, and the user isn't logged in or isn't using one of the roles
            if ((!$user || $user->isNobody()) || (!(in_array($user->role()->name(), $requiredRoles)))) {
                return false;
            }
        }

        // If no user is logged in (or it's the kirby user), we will check for required roles later.
        // If roles are required and no eligible user is logged in, access will be denied.
        $applicableRoles = [];

        // Traverse up the page hierarchy to find the first non-empty requiredRoles field
        $page = $currentPage;
        while ($page) {
            $currentRoles = $this->getPageFieldAsArray($page,'requiredRoles'); // Access the field
            // If this page has required roles set, these are the applicable roles due to inheritance
            if (!empty($currentRoles)) {
                $applicableRoles = $currentRoles;
                break; // Found the most specific required roles in the hierarchy
            }

            // Move up to the parent page
            $page = $page->parent();
        }

        // Now check if the user has permission based on the applicable roles found

        // If no applicable roles were found throughout the hierarchy, access is allowed
        if (empty($applicableRoles)) {
            return true;
        }

        // If applicable roles exist, check if a *non-kirby* user is logged in
        // and if their role is in the list
        if ($user && !$user->isKirby() && in_array($user->role()->name(), $applicableRoles)) {
            return true;
        }

        // If none of the above conditions are met, the user does not have permission
        return false;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    protected function isUserLoggedIn(): bool {
        return $this->kirby->user() != null;
    }

    /**
     * @return LoginDetails
     * @noinspection PhpUnused
     */
    protected function getLoginDetails() : LoginDetails {
        $loginDetails = new LoginDetails();
        $loginDetails->setRedirectPage(get('redirectPage', ''));
        // handle the form submission
        if ($this->kirby->request()->is('POST') && get('userName')) {
            $loginDetails->setHasBeenProcessed(true);
            // try to log the user in with the provided credentials
            try {
                // validate CSRF token
                if (csrf(get('csrf')) === true) {
                    $userName = trim(get('userName'));
                    $userName = str_replace(' ', '-', $userName);
                    $loginDetails->setUserName($userName);

                    $this->kirby->auth()->login($userName, trim(get('password')), true);
                    $loginDetails->setLoginStatus(true);
                    $loginDetails->setLoginMessage('You have successfully logged in');
                    if ($loginDetails->hasRedirectPage()) {
                        go($loginDetails->getRedirectPage());
                    }
                    $this->redirectToHome();
                } else {
                    $loginDetails->setLoginStatus(false);
                    $loginDetails->setLoginMessage('Your security token has expired - please login again.');
                }
            } catch (Exception) {
                $loginDetails->setLoginStatus(false);
                $loginDetails->setLoginMessage('Login failed. Please check your username and password.');
            }
        }

        $csrfToken = csrf();
        $loginDetails->setCSRFToken($csrfToken);

        return $loginDetails;
    }

    #endregion

    #region TAGS_AND_FILTERS

    /**
     * @param Collection $pages
     * @param string $tagName
     * @param string $tagValue
     * @return Collection
     * @noinspection PhpUnused
     */
    protected function filterByPagesTag(Collection $pages, string $tagName, string $tagValue): Collection {
        if (empty($tagValue)) { return $pages;}
        return $pages->filter(function ($page) use ($tagName, $tagValue) {
            $pages = $page->content()->get($tagName)->toPages();
            return ($pages->filterBy('title', $tagValue)->count() > 0);
        });
    }

    /**
     * @param Structure $structure
     * @param string $tagName
     * @param string $tagValue
     * @return Structure
     * @noinspection PhpUnused
     */
    protected function filterStructureByPagesTag(Structure $structure, string $tagName, string $tagValue): Structure {
        if (empty($tagValue)) { return $structure;}
        return $structure->filter(function ($structureItem) use ($tagName, $tagValue) {
            $pages = $structureItem->content()->get($tagName)->toPages();
            if ($pages->isNotEmpty()) {
                return ($pages->filterBy('title', $tagValue)->count() > 0);
            }
            return false;
        });
    }

    /**
     * @param Page $kirbyPage
     * @param string $tagType
     * @param string $fieldName
     * @return WebPageTagLinkSet
     */
    protected function getWebPageTagLinkSet(Page $kirbyPage, string $tagType, string $fieldName): WebPageTagLinkSet
    {
        $tagLinkSet = new WebPageTagLinkSet();
        $tagLinkSet->setTagType($tagType);
        $tagLinkSet->setLinks($this->getPageFieldAsWebPageLinks($kirbyPage,$fieldName));
        return $tagLinkSet;
    }

    /**
     * @param Page $kirbyPage
     * @return WebPageTagLinks
     */
    protected function getTagLinks(Page $kirbyPage) : WebPageTagLinks {
        $tagFields = $this->getFieldsInSection($kirbyPage, 'tags');
        $tags = new WebPageTagLinks();
        foreach ($tagFields as $tagField) {
            $tagLinks = $this->getWebPageTagLinkSet($kirbyPage, $tagField['label'], $tagField['name']);
            if ($tagLinks->hasLinks()) {
                $tags->addListItem($tagLinks);
            }
        }
        return $tags;
    }


    /**
     * @throws KirbyRetrievalException
     * @throws InvalidArgumentException
     * @noinspection PhpUnused
     */
    public function syncTags($template) : string {
        $logFile = 'tag-sync';

        $this->writeToLog( $logFile, 'Starting sync of tags for template: '.$template);


        $tagMapping = option('tagMapping');
        $sitePages = $this->site->index()->filterBy('template', $template); //['product'])

        $i = 0;
        foreach ($sitePages as $page) {
            if (array_key_exists($page->template()->name(), $tagMapping))
            {
                $lastSyncTimestamp = $page->lastTagSync()->toTimestamp();
                $currentTime = time();
                // Calculate the time difference in minutes
                $timeDifferenceMinutes = round(abs($currentTime - $lastSyncTimestamp) / 60);
                if ($lastSyncTimestamp > 0 && $timeDifferenceMinutes < 30) {
                    $logMessage = "Skipped (synced " . $timeDifferenceMinutes . " mins ago)";
                } else {
                    $logMessage = $this->handleTwoWayTagging($page, null, false);

                }
                $this->writeToLog($logFile, $page->title() . '-' . $logMessage . "\n");

                if (($i % 50) === 0) { // Example: run GC every 50 pages
                    $collected = gc_collect_cycles();
                    $this->writeToLog(
                        $logFile,
                        "GC collected $collected cycles. Current memory: "
                        . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n",
                    );
                }
                $i++;
            }
        }
        $this->writetoLog($logFile, "Completed sync of tags...");
        return 'COMPLETE';
    }


    /**
     * Generic helper function to manage two-way tagging between pages.
     * When the 'taggingPage' (the page being created/updated) uses `taggingField`
     * to link to other pages, this function ensures those 'tagged pages' have their
     * `taggedField` updated to reflect the link to the 'taggingPage'.
     *
     * @param Page $taggingPage The page that was created or updated (e.g. a vacancy page).
     * @param Page|null $oldTaggingPage The old version of the tagging page (null for creation).
     * @param bool $singlePage
     * @return string
     * @throws InvalidArgumentException
     * @throws KirbyRetrievalException
     */
    public function handleTwoWayTagging(
        Page  $taggingPage,
        ?Page $oldTaggingPage = null,
        bool  $singlePage = true
    ):string {
        // Static flag to prevent re-entry for the current request lifecycle
        static $isSyncing = false;

        // If the function is already running, exit to prevent infinite loops/re-entry
        if ($isSyncing) {
            return 'Already syncing, skipping re-entry.';
        }

        $isSyncing = true; // Set the flag

        $log = '';

        if ($singlePage) {
            kirby()->cache('pages')->flush();
        }

        // Get the unique ID of the page that was created or updated
        $taggingPageId = $taggingPage->id();

        $taggingFields = $this->getFieldsInSection($taggingPage, 'tags-fields');

        if (count($taggingFields) === 0) {
            $taggingFields = $this->getFieldsInSection($taggingPage, 'tags');
        }

        if (count($taggingFields) === 0) {
            return $log;
        }

        try {
            $tagMapping = option('tagMapping');
        }
        catch (Throwable) {
            $isSyncing = false;
            throw new KirbyRetrievalException('Tag mapping config not set up');
        }

        try {
            $taggedByField = $tagMapping[$taggingPage->template()->name()];
        } catch (Throwable) {
            $isSyncing = false;
            throw new KirbyRetrievalException('Tag mapping config not set up for ' . $taggingPage->template()->name());
        }

        foreach ($taggingFields as $taggingField) {
            $taggingFieldName = $taggingField['name'];

            // Get the current list of linked page IDs from the $taggingPage
            $newLinkedPageIds = $taggingPage->{$taggingFieldName}()->isNotEmpty()
                ? $taggingPage->{$taggingFieldName}()->toPages()->pluck('id')
                : [];

            // Get the old list of linked page IDs from $oldTaggingPage for removals
            // If $oldTaggingPage is not provided, assume no old links for removal tracking
            $oldLinkedPageIds = ($oldTaggingPage && $oldTaggingPage->{$taggingFieldName}()->isNotEmpty())
                ? $oldTaggingPage->{$taggingFieldName}()->toPages()->pluck('id')
                : [];

            // Determine which links were removed based on $oldTaggingPage
            $removedLinkedPageIds = array_diff($oldLinkedPageIds, $newLinkedPageIds);

            // --- Handle all current links on $taggingPage (add if not present) ---
            foreach ($newLinkedPageIds as $linkedPageId) {
                $linkedPage = kirby()->page($linkedPageId);

                if ($linkedPage) {
                    // Get existing IDs from the linked page's `taggedField`
                    $existingTaggingPageIds = $linkedPage->{$taggedByField}()->isNotEmpty()
                        ? $linkedPage->{$taggedByField}()->toPages()->pluck('id')
                        : [];

                    // If the $taggingPageId is not already in the linked page's field, add it
                    if (!in_array($taggingPageId, $existingTaggingPageIds)) {
                        $existingTaggingPageIds[] = $taggingPageId;

                        $blueprint = $linkedPage->blueprint(); // Get the blueprint object for the page
                        $fields = $blueprint->fields();        // Get all fields defined in the blueprint

                        // Check if the field exists within the blueprint's fields array
                        if (isset($fields[$taggedByField])) {
                            // Update the linked page with the modified list of IDs
                            try {
                                $linkedPage->update([
                                    $taggedByField => array_unique($existingTaggingPageIds) // Ensure uniqueness
                                ]);
                                $log .= "$taggingPageId added to $taggedByField on $linkedPageId";
                            } catch (Throwable $e) {
                                $isSyncing = false;
                                throw new KirbyRetrievalException(
                                    "Error adding $taggingPageId to $taggedByField on $linkedPageId: "
                                    . $e->getMessage()
                                );
                            }
                        } else {
                            $isSyncing = false;
                            throw new KirbyRetrievalException(
                                'Tag field '.$taggedByField
                                .' has not been set up in the '.$linkedPage->template()->name()
                                . ' blueprint  linked page '.$linkedPage->title()
                            );
                        }
                    }
                } else {
                    // Log or handle cases where a linked page from $newLinkedPageIds is not found
                    $log.= "Warning: Linked page with ID '$linkedPageId' not found for tagging page '$taggingPageId'.";
                }
            }

            // --- Handle links that were removed (based on $oldTaggingPage) ---
            foreach ($removedLinkedPageIds as $linkedPageId) {
                // Attempt to find the corresponding page by its ID
                $linkedPage = kirby()->page($linkedPageId);

                // If the linked page exists, update its `taggedField`
                if ($linkedPage) {
                    // Get existing IDs from the linked page's `taggedField`
                    $existingTaggingPageIds = $linkedPage->{$taggedByField}()->isNotEmpty()
                        ? $linkedPage->{$taggedByField}()->toPages()->pluck('id')
                        : [];

                    // Filter out the current $taggingPageId from the list
                    // Only remove if it was truly linked by this tagging page
                    if (in_array($taggingPageId, $existingTaggingPageIds)) {
                        $updatedTaggingPageIds = array_filter(
                            $existingTaggingPageIds, fn($id) => $id !== $taggingPageId)
                        ;

                        $blueprint = $linkedPage->blueprint(); // Get the blueprint object for the page
                        $fields = $blueprint->fields();        // Get all fields defined in the blueprint

                        // Check if the field exists within the blueprint's fields array
                        if (isset($fields[$taggedByField])) {


                            // Update the linked page with the modified list of IDs
                            try {
                                $linkedPage->update([
                                    $taggedByField => array_unique($updatedTaggingPageIds)
                                ]);
                                $log .= "$taggingPageId removed from $taggedByField on $linkedPageId";
                            } catch (Throwable $e) {
                                $isSyncing = false;
                                throw new KirbyRetrievalException(
                                    "Error removing $taggingPageId from $taggedByField on $linkedPageId: "
                                    . $e->getMessage()
                                );
                            }
                        } else {
                            $isSyncing = false;
                            throw new KirbyRetrievalException(
                                'Tag field '.$taggedByField.' has not been set up on linked page '
                                . $linkedPage->title()
                            );
                        }
                    }
                } else {
                    // Log or handle cases where a linked page from $removedLinkedPageIds is not found
                    $log.= "Warning: Linked page with ID '$linkedPageId' not found for tagging page '$taggingPageId'";
                }
            }
        }
        if (!$singlePage) {
            try {
                // Re-fetch the taggingPage to ensure it's the latest version
                $taggingPage = kirby()->page($taggingPage->id());
                $taggingPage->update([
                    'lastTagSync' => date('Y-m-d H:i:s') // Use a format suitable for the datetime field
                ]);
            } catch (Throwable $e) {
                $isSyncing = false;
                throw new KirbyRetrievalException(
                    'Error updating lastTagSync on ' . $taggingPage->title() . ': ' . $e->getMessage()
                );
            }
        }
        $isSyncing = false;
        return $log;
    }

    /**
     * @param int $limit
     * @param Collection $pages
     * @return Collection
     * @noinspection PhpUnused
     */
    public function applyLimit(Collection $pages, int $limit): Collection {
        if ($limit>0) {
            $pages = $pages->limit($limit);
        }
        return $pages;
    }


    #endregion

    #region LANGUAGES

    /**
     * @return Languages
     * @throws KirbyRetrievalException
     */
    protected function getLanguages() : Languages {

        $defaultLanguage = $this->kirby->defaultLanguage();
        $languages = new Languages();
        if ($defaultLanguage !== null) {
            $languages->setUsingDefaultLanguage($this->kirby->language()->code() === $defaultLanguage->code());
            $languages->setEnabled(true);
            $languages->setCurrentLanguage($this->kirby->language()->name());
            $languagesFromKirby = $this->kirby->languages();

            foreach ($languagesFromKirby as $lang) {
                try {
                    $translatedPage = $this->page->translation($lang->code());
                } catch (NotFoundException $e) {
                    throw new KirbyRetrievalException('The language could not be found: '.$e->getMessage());
                }

                if ($translatedPage->exists()) {
                    $language = new Language();
                    $language->setIsDefault($lang->code() === $defaultLanguage->code());
                    if (!$language->isDefault()) {
                        $languages->setIsPageTranslatedInCurrentLanguage(true);
                    }
                    $language->setIsActivePage($lang->code() === kirby()->language()->code());
                    $language->setCode($lang->code());
                    $language->setName($lang->name());
                    /** @var Page $pageModel */
                    $pageModel = $translatedPage->model();
                    $language->setCurrentPageUrl($pageModel->url($lang->code()));
                    $languages->addLanguage($language);
                }

            }
        }
        return $languages;
    }
    #endregion

    #region CACHING
    /**
     * @param Page $page
     * @return string
     * @noinspection PhpUnused
     */
    public function handleCaches(Page $page) : string {
        $cacheName = option('cacheName');
        $cacheMapping = option('cacheMapping');

        if (array_key_exists($page->template()->name(), $cacheMapping)) {
            try {
                $cache = $this->kirby->cache($cacheName);
            } catch (InvalidArgumentException) {
                return 'Failed to get cache';
            }
            $cacheKey = $cacheMapping[$page->template()->name()];
            $cache->remove($cacheKey);
        }
        return 'Success';
    }
    #endregion

    #region REDIRECTS

    /**
     * @param Page $page
     * @return void
     */
    public function redirectToFile(Page $page):void {
        $file = $this->getPageFieldAsDocument($page, 'file');
        go($file->getUrl());
    }

    /**
     * @param Page $page
     * @return void
     * @throws KirbyRetrievalException
     */
    public function redirectToPage(Page $page):void {
        $redirectLink = $this->getPageFieldAsUrl($page,'redirect_link', true);
        go($redirectLink );
    }


    /**
     * @param string $redirectPage
     * @return void
     */
    protected function redirectToLogin(string $redirectPage=''): void
    {
        $loginPage = $this->findKirbyPage('login');
        $url = $loginPage->url();

        if (!empty($redirectPage)) {
            $url .= '?' . http_build_query(['redirectPage' => $redirectPage]);
        }
        go($url);
    }

    /**
     * @return void
     */
    protected function redirectToHome(): void
    {
        $homePage = $this->findKirbyPage('home');
        $homePage->go();
    }

    #endregion

    #region TURNSTILE

    /**
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getTurnstileSiteKey(): string {
        $turnstileSiteKey = (string) option('turnstile.siteKey');

        if (empty($turnstileSiteKey)) {
            throw new KirbyRetrievalException('The Turnstile site key for Uniform is not configured');
        }
        return $turnstileSiteKey;
    }

    /**
     * @throws KirbyRetrievalException
     */
    protected function getTurnstileResponse(): void {

        // Turnstile HTML input field name
        $fieldName = 'cf-turnstile-response';

        // URL for the Turnstile verification
        $verificationUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $turnstileChallenge = $this->kirby->request()->get($fieldName);

        if (empty($turnstileChallenge)) {
            throw new KirbyRetrievalException('The Turnstile secret key is not configured');
        }

        $secretKey = option('turnstile.secretKey');

        if (empty($secretKey)) {
            throw new KirbyRetrievalException('The Turnstile secret key is not configured');
        }

        try {
            $response = Remote::request($verificationUrl, [
                'method' => 'POST',
                'data' => [
                    'secret' => $secretKey,
                    'response' => $turnstileChallenge,
                ],
            ]);
        } catch (\Exception) {
            throw new KirbyRetrievalException('Error when trying to verify the Turnstile secret key');
        }

        $jsonResponse = $response->json();

        if ($response->code() !== 200 || !isset($jsonResponse['success']) || $jsonResponse['success'] !== true) {
            throw new KirbyRetrievalException('Turnstile rejected this input');
        }
    }


    #endregion

    #region MISC

    /**
     * gets the colour mode (getting/setting a colourMode cookie as required)
     * @return string
     * @throws KirbyRetrievalException
     */
    protected function getColourMode(): string
    {
        $colourMode = get('colourMode') ?: Cookie::get('colourMode') ?: 'auto';

        if (!is_string($colourMode)) {
            throw new KirbyRetrievalException('site controller: $colourMode is not set to a string');
        }

        if (get('colourMode')) {
            $this->setCookie('colourMode', $colourMode);
        }
        return $colourMode;
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function setCookie(string $key, string $value): void
    {
        //allow insecure cookies on localhost only
        $secure = $_SERVER['HTTP_HOST'] != 'localhost:8095';
        Cookie::set(
            $key,
            $value,
            ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'secure' => $secure, 'httpOnly' => true]
        );
    }




    /**
     * @param string $fullClassName
     * @return string
     */
    private function extractClassName(string $fullClassName ): string {
        $afterLastBackslash = strrchr($fullClassName, '\\');
        if ($afterLastBackslash === false) {
            // No backslash found, the string is the content
            return $fullClassName;
        } else {
            // Remove the leading backslash
            return substr($afterLastBackslash, 1);
        }
    }


    /**
     * @param string $optionKey
     * @return string
     * @noinspection PhpUnused
     */
    public function getOption(string $optionKey): string
    {
        $optionValue = $this->kirby->option($optionKey);
        return $this->asString($optionValue);
    }

    /**
     * @param Page $page
     * @param string $sectionName
     * @return array
     */
    protected function getFieldsInSection(Page $page, string $sectionName): array
    {

        $blueprint = $page->blueprint();

        $fieldNamesInSection = [];

        $sections = $blueprint->sections();

        if (isset($sections[$sectionName])) {
            $section = $sections[$sectionName];
        } else if (isset($sections[$sectionName.'-fields'])) {
            $section = $sections[$sectionName.'-fields'];
        }
        if (isset($section) && $section->fields()) {

            foreach ($section->fields() as $fieldName => $fieldDefinition) {
                if ($fieldDefinition['type'] !== 'info') {
                    $fieldNamesInSection[] = ['name' => $fieldName, 'label' => $fieldDefinition['label']];
                }
            }
        }

        return $fieldNamesInSection;

    }


    /**
     * @param mixed $value
     * @param string $fallback
     * @return string
     */
    protected function asString(mixed $value, string $fallback = ''): string
    {
        if (is_string($value)) {
            return $value;
        } else if (is_array($value)) {
            return implode(', ', $value);
        } elseif (is_null($value)) {
            return $fallback;
        } else {
            return (string) $value;
        }
    }


    /**
     * Get the default fallback datetime
     * where a date is required, but the overall
     * operation has failed.
     * @return DateTime
     * @noinspection PhpUnused
     */
    protected function getFallBackDateTime(): DateTime
    {
        return new DateTime('1970-01-01 00:00:00');
    }

    /**
     * @return void
     * @noinspection PhpUnused
     */
    protected function logCurrentTime(): void
    {
        $microtime = microtime(true);
        $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);
        $timestamp = (new DateTime())->setTimestamp((int)$microtime)->format("Y-m-d H:i:s");
        echo "[$timestamp.$milliseconds]<br>";
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     * @throws DateMalformedStringException
     * @throws Throwable
     */
    public function publishScheduledPages(): string
    {

        if ($this->isSiteFieldNotEmpty('scheduled')) {
            $scheduledEntries = $this->getSiteFieldAsStructure('scheduled');
            $updatedList = [];
            $publishedCount = 0;

            $this->kirby->impersonate('kirby');

            foreach ($scheduledEntries as $entry) {
                // Get the collection of Page objects from the field
                $pages = $entry->page()->toPages();

                // If there's at least one page selected
                if ($pages->isNotEmpty()) {
                    $scheduledDate = $entry->scheduledPublishDate()->value();
                    $scheduledTime = $entry->scheduledPublishTime()->value();

                    // If a date and time exist for the entry
                    if ($scheduledDate && $scheduledTime) {
                        $timezone = new DateTimeZone('Europe/London');
                        $scheduledDateTime = new DateTime(
                            $scheduledDate . ' ' . $scheduledTime,
                            $timezone
                        );
                        $currentDateTime = new DateTime('now', $timezone);

                        // The comparison should now work reliably
                        if ($currentDateTime >= $scheduledDateTime) {
                            // Loop through each selected page
                            foreach ($pages as $page) {
                                try {
                                    if ($page) {
                                        if ($page->isListed()) {
                                            $this->writeToLog(
                                                'scheduledPublish',
                                                'Page already published: ' . $page->title()
                                                    . ' at ' . $currentDateTime->format('Y-m-d H:i:s') . PHP_EOL
                                            );
                                            // Continue to the next page in the loop
                                            continue;
                                        }
                                        // If not already listed, change the status
                                        $page->changeStatus('listed');
                                        $this->writeToLog('scheduledPublish',
                                            'Published ' . $page->title() . ' at '
                                            . $currentDateTime->format('Y-m-d H:i:s') . PHP_EOL
                                        );
                                        $publishedCount++;
                                    }
                                } catch (\Exception $e) {
                                    $this->writeToLog('scheduledPublish',
                                        'Error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString() . PHP_EOL);
                                    return 'Error:' . $e->getMessage() . ' - ' . $e->getTraceAsString() . PHP_EOL;
                                }
                            }
                        } else {
                            // Keep the entry in the list if it's not ready to be published
                            $updatedList[] = $entry->content()->toArray();
                        }
                    }
                }
            }

            // Encode and save the new, updated list back to the site file
            $this->site->update([
                'scheduled' => Yaml::encode($updatedList),
            ]);

            return 'Scheduled pages processed. Published ' . $publishedCount . ' pages.';
        }
        return '';
    }

    #endregion

}
