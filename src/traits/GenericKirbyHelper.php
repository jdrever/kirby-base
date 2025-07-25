<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\helpers\KirbyRetrievalException;
use BSBI\WebBase\models\ActionStatus;
use BSBI\WebBase\models\BaseFilter;
use BSBI\WebBase\models\BaseList;
use BSBI\WebBase\models\BaseModel;
use BSBI\WebBase\models\BaseWebPage;
use BSBI\WebBase\models\CoreLink;
use BSBI\WebBase\models\Document;
use BSBI\WebBase\models\Image;
use BSBI\WebBase\models\ImageType;
use BSBI\WebBase\models\Pagination;
use BSBI\WebBase\models\RelatedContent;
use BSBI\WebBase\models\WebPageBlock;
use BSBI\WebBase\models\WebPageBlocks;
use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;
use BSBI\WebBase\models\User;
use BSBI\WebBase\models\WebPageTagLinkSet;
use DateTime;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Structure;
use Kirby\Cms\StructureObject;
use Kirby\Content\Field;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Cookie;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;


/** @deprecated  */
trait GenericKirbyHelper
{

#region IMAGES

    /**
     * @param Page $page
     * @param string $fieldName
     * @param int $width
     * @param ?int $height
     * @param ImageType $imageType
     * @return Image
     */
    private function getImage(Page $page, string $fieldName, int $width, ?int $height, ImageType $imageType = ImageType::SQUARE): Image
    {
        try {
            $pageImage = $this->getPageFieldAsFile($page, $fieldName);
            if ($pageImage != null) {

                $src = $pageImage->crop($width, $height)->url();
                $srcSetType = ($imageType === ImageType::SQUARE) ? 'square' : 'main';
                $srcSet = $pageImage->srcset($srcSetType);
                $webpSrcSet = $pageImage->srcset($srcSetType . '-webp');
                /** @noinspection PhpUndefinedMethodInspection */
                $alt = $pageImage->alt()->isNotEmpty() ? $pageImage->alt()->value() : '';
                if ($src !== null && $srcSet !== null && $webpSrcSet !== null) {
                    return new Image ($src, $srcSet, $webpSrcSet, $alt, $width, $height);
                }

            }
            return (new Image())->recordError('Image not found');
        } catch (KirbyRetrievalException) {
            return (new Image())->recordError('Image not found');
        }
    }
#endregion

#region FIELDS

    /**
     * Gets the field - if the field is empty, returns an empty string
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException if the page or field cannot be found
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
     * @throws KirbyRetrievalException if the page or field cannot be found
     */
    public function getPageTitle($page): string
    {
        return $page->title()->toString();
    }

    /**
     * Gets the field - if the field is empty, returns an empty string
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException if the page or field cannot be found
     */
    private function getPageFieldAsString(Page $page, string $fieldName, bool $required = false): string
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            return $pageField->toString();
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
     * @return int
     * @throws KirbyRetrievalException
     */
    private function getPageFieldAsInt(Page $page, string $fieldName, bool $required = false): int
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
     */
    private function getPageFieldAsFloat(Page $page, string $fieldName, bool $required = false): float
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
     * @return bool
     * @throws KirbyRetrievalException
     */
    private function getPageFieldAsBool(Page $page, string $fieldName, bool $required = false): bool
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            return $pageField->toBool();
        } catch (KirbyRetrievalException $e) {
            if ($required) {
                throw $e;
            }
            //TODO: should be better than returning false if not required.  Maybe return bool|null
            return false;
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return ?DateTime
     * @throws KirbyRetrievalException
     */
    private function getPageFieldAsDateTime(Page $page, string $fieldName, bool $required = false): ?DateTime
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
     */
    private function getPageFieldAsRequiredDateTime(Page $page, string $fieldName): DateTime
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return (new DateTime())->setTimestamp($pageField->toDate());
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return Structure
     * @throws KirbyRetrievalException
     */
    private function getPageFieldAsStructure(Page $page, string $fieldName): Structure
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $pageField->toStructure();
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getPageFieldAsUrl(Page $page, string $fieldName, bool $required = false): string
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
     */
    private function getPageFieldAsBlocksHtml(Page $page, string $fieldName, bool $required = false, int $excerpt = 0): string
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
    private function getPageFieldAsBlocks(Page $page, string $fieldName): Blocks
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
    private function getPageFieldAsArray(Page $page, string $fieldName, bool $required = false): array
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
     */
    private function getPageFieldAsKirbyText(Page $page, string $fieldName, bool $required = false): string
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
    private function getPageFieldAsFile(Page $page, string $fieldName): File|null
    {
        $pageField = $this->getPageField($page, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $pageField->toFile();
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return WebPageLinks
     */
    private function getPageFieldAsWebPageTagLinks(Page $page, string $fieldName): WebPageLinks
    {
        try {
            $pageField = $this->getPageField($page, $fieldName);
            /** @noinspection PhpUndefinedMethodInspection */
            $pages = $pageField->toPages();
            return $this->getWebPageLinks($pages);
        } catch (KirbyRetrievalException $e) {
            return new WebPageLinks();
        }
    }

    /**
     * @param Page $page
     * @param string $fieldName
     * @return bool
     */
    private function isPageFieldNotEmpty(Page $page, string $fieldName): bool
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
    private function getPageField(Page $page, string $fieldName): Field
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
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getSiteFieldAsString(string $fieldName, bool $required = false): string
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
     * @return Structure
     * @throws KirbyRetrievalException
     */
    private function getSiteFieldAsStructure(string $fieldName): Structure
    {
        $siteField = $this->getSiteField($fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $siteField->toStructure();
    }

    /**
     * @param string $fieldName
     * @return Page
     * @throws KirbyRetrievalException
     */
    private function getSiteFieldAsPage(string $fieldName): Page
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
    private function getSiteField(string $fieldName): Field
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
     * @param StructureObject $structure
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getStructureFieldAsString(StructureObject $structure, string $fieldName, bool $required = false): string
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
     * @return bool
     * @throws KirbyRetrievalException
     */
    private function getStructureFieldAsBool(StructureObject $structure, string $fieldName): bool
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $structureField->toBool();
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getStructureFieldAsUrl(StructureObject $structure, string $fieldName): string
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        $page = $structureField->toPage();
        if ($page) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $structureField->toUrl();
        } else {
            return '';
        }
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getStructureFieldAsPageTitle(StructureObject $structure, string $fieldName): string
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        $page = $structureField->toPage();
        if ($page) {
            return $page->title()->value();
        } else {
            return '';
        }
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getStructureFieldAsPageUrl(StructureObject $structure, string $fieldName): string
    {
        $structureField = $this->getStructureField($structure, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $structureField->toPage()->url();
    }

    /**
     * @param StructureObject $structure
     * @param string $fieldName
     * @return Field
     * @throws KirbyRetrievalException
     */
    private function getStructureField(StructureObject $structure, string $fieldName): Field
    {
        //TODO: sort out this code to get the value back
        $structureField = $structure->content()->get($fieldName);
        if (!$structureField instanceof Field || $structureField->isEmpty()) {
            throw new KirbyRetrievalException('Structure field not found or empty');
        }
        return $structureField;
    }

    #endregion

    #region BLOCKS

    /**
     * @param Block $block
     * @param string $fieldName
     * @param bool $required
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getBlockFieldAsString(Block $block, string $fieldName, bool $required = false): string
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
     */
    private function getBlockFieldAsInt(Block $block, string $fieldName, bool $required = false): int
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
     */
    private function getBlockFieldAsImage(Block $block, string $fieldName, int $width, ?int $height, ImageType $imageType = ImageType::SQUARE, bool $fixedWidth = false): Image
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
     */
    private function getBlockFieldAsBlocks(Block $block, string $fieldName, bool $required = false): string
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
     */
    private function getBlockFieldAsBlocksHtml(Block $block, string $fieldName, bool $required = false): string
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
    private function isBlockFieldNotEmpty(Block $block, string $fieldName): bool
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
    private function getBlockField(Block $block, string $fieldName): Field
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
    private function getBlockFieldAsFile(Block $block, string $fieldName): File|null
    {
        $blockField = $this->getBlockField($block, $fieldName);
        /** @noinspection PhpUndefinedMethodInspection */
        return $blockField->toFile();
    }

    #endregion

    #region PAGES

    /**
     * @param Page $page
     * @param string $pageClass the type of class to return (must extend BaseWebPage)
     * @param bool $checkUserRoles
     * @return BaseWebPage
     */
    private function getPage(Page $page, string $pageClass = BaseWebPage::class, bool $checkUserRoles = true): BaseWebPage
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
                if (!$webPage->checkUserAgainstRequiredRoles()) {
                    $this->redirectToLogin();
                }
            }

            $webPage->setDescription(
                $this->isPageFieldNotEmpty($page, 'description')
                    ? $this->getPageFieldAsString($page, 'description')
                    : $this->getSiteFieldAsString('description')
            );

            $webPage->setBreadcrumb($this->getBreadcrumb());

            $webPage->setMenuPages($this->getMenuPages());

            $webPage->setSubPages($this->getSubPages($page, $webPage->isUsingSimpleLinksForSubPages()));

            if ($this->isPageFieldNotEmpty($page, 'mainContent')) {
                $webPage->setMainContentBlocks($this->getContentBlocks($page));
            }

            if ($this->isPageFieldNotEmpty($page, 'lowerContent')) {
                $webPage->setLowerContentBlocks($this->getContentBlocks($page, 'lowerContent'));
            }

            if ($this->isPageFieldNotEmpty($page, 'related')) {
                $relatedContent = $this->getPageFieldAsStructure($page, 'related');
                foreach ($relatedContent as $item) {
                    $itemTitle = $this->getStructureFieldAsString($item, 'title', false);
                    if (!empty($itemTitle)) {
                        $content = new RelatedContent(
                            strval($item->title()),
                            $this->getStructureFieldAsUrl($item, 'url'),
                            $this->getStructureFieldAsBool($item, 'openInNewTab')
                        );
                        $webPage->addRelatedContent($content);
                    }
                }
            }


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

    private function redirectToLogin(): void
    {
        $loginPage = $this->findKirbyPage('login');
        $loginPage->go();
    }

    /**
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    public function getMenuPages(): WebPageLinks
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
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    private function getSubPages(Page $page, bool $simpleLink = true): WebPageLinks
    {
        $subPagesCollection = $this->getSubPagesAsCollection($page);
        if ($subPagesCollection instanceof Collection) {
            return $this->getWebPageLinks($subPagesCollection, $simpleLink);
        }
        return new WebPageLinks();
    }


    /**
     * get the pages below the current page (excluding certain template types)
     * if home page, return menu pages without the home page itself
     * @return Pages|\Kirby\Toolkit\Collection|null
     */
    private function getSubPagesAsCollection(Page $page): mixed
    {
        if ($page->template()->name() !== 'home') {
            //TODO: need to not hard-code option location
            $excludedTemplates = option('bsbi.bsbiweb.subPagesExclude');

            // Ensure it returns an array
            if (!is_array($excludedTemplates)) {
                $excludedTemplates = [];
            }
            return $page->children()->notTemplate($excludedTemplates);
        }
        $menuPages = $this->kirby->collection('menuPages');
        if ($menuPages instanceof Collection) {
            return $menuPages->filterBy('template', '!=', 'home');
        }
        return null;
    }

    private function getSubPageLink(Page $page, string $template): WebPageLink
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
     * @return Pages
     */
    private function getSubPagesUsingTemplates(Page $page, array $templates): Pages
    {
        return $page->children()->template($templates);
    }


    /**
     * @param string $title
     * @return BaseWebPage
     */
    private function findPage(string $title): BaseWebPage
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
     * @param string $title
     * @param Page|null $parentPage
     * @return Page|null
     */
    private function findKirbyPage(string $title, ?Page $parentPage = null): Page|null
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
     * @param string $title
     * @param string $linkType
     * @return CoreLink
     */
    private function findCoreLink(string $title, string $linkType): CoreLink
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
     */
    private function getCoreLink(Page $page, string $linkType): CoreLink
    {
        if ($page instanceof Page) {
            $coreLink = new CoreLink($page->title()->toString(), $page->url(), $linkType);
        } else {
            $coreLink = new CoreLink('', '', 'NOT_FOUND');
            $coreLink->setStatus(false);
        }
        return $coreLink;
    }

    /**
     * @param string $pageId
     * @return Page
     * @throws KirbyRetrievalException
     */
    private function getKirbyPage(string $pageId): Page
    {
        $page = $this->kirby->page($pageId);
        if (!$page instanceof Page) {
            throw new KirbyRetrievalException('Page not found');
        }
        return $page;
    }

    /**
     * @param \Kirby\Toolkit\Collection $collection
     * @param bool $simpleLink
     * @return WebPageLinks
     * @throws KirbyRetrievalException
     */
    private function getWebPageLinks(\Kirby\Toolkit\Collection $collection, bool $simpleLink = true): WebPageLinks
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
     * @return WebPageLink
     * @throws KirbyRetrievalException
     */
    private function getWebPageLink(Page $page, bool $simpleLink = true): WebPageLink
    {
        $templateName = $page->template()->name();
        if ($templateName === 'file_link') {
            $file = $this->getPageFieldAsFile($page, 'file');
            $pageUrl = $file ? $file->url() : '';
        } else {
            $pageUrl = $page->url();
        }
        $webPageLink = new WebPageLink($page->title()->toString(), $pageUrl , $page->id(), $page->template()->name());
        $webPageLink->setDescription($this->getPageFieldAsString($page, 'description'));
        $webPageLink->setDescription($this->getPageFieldAsKirbyText($page, 'panelDescription'));
        if ($simpleLink) {
            return $webPageLink;
        }
        if ($this->isPageFieldNotEmpty($page, 'panelImage')) {
            $panelImage = $this->getImage($page, 'panelImage', 300, 300, ImageType::SQUARE);
            $webPageLink->setImage($panelImage);
        }
        $webPageLink->setSubPages($this->getSubPages($page));
        return $webPageLink;
    }

    /**
     * @param string $collectionName
     * @return \Kirby\Toolkit\Collection
     * @throws KirbyRetrievalException
     */
    private function getPagesFromCollection(string $collectionName): \Kirby\Toolkit\Collection
    {
        $pages = $this->kirby->collection($collectionName);
        if (!isset($pages)) {
            throw new KirbyRetrievalException('Collection ' . $collectionName . ' pages not found');
        }
        return $pages;
    }

    /**
     * @deprecated
     * @param string $collection
     * @param string $modelListClass
     * @param class-string<BaseModel> $modelClass
     * @param BaseFilter|null $filter
     * @param callable|null $filterFunction
     * @param callable|null $setModelFunction
     * @return BaseModel
     * @throws KirbyRetrievalException
     */
    private function getModelList(string      $collection,
                                  string      $modelListClass = BaseModel::class,
                                  string      $modelClass = BaseModel::class,
                                  ?BaseFilter $filter = null,
                                  ?callable   $filterFunction = null,
                                  ?callable    $setModelFunction = null): BaseModel
    {


        // Ensure $pageClass is a subclass of WebPage
        if (!(is_a($modelListClass, BaseModel::class, true))) {
            throw new KirbyRetrievalException("Model list class must extend BaseModel.");
        }

        $modelList = new $modelListClass();

        // Check if the created $modelList instance has the addListItem function
        if (!method_exists($modelList, 'addListItem')) {
            throw new KirbyRetrievalException("The class {$modelClass} does not have an addListItem function.");
        }

        // Check if the created $modelList instance has the addListItem method
        if (!method_exists($modelList, 'setFilters')) {
            throw new KirbyRetrievalException("The class {$modelClass} does not have an setFilters function.");
        }

        // Ensure $modelClass is a subclass of BaseModel
        if (!(is_a($modelClass, BaseModel::class, true))) {
            throw new KirbyRetrievalException("Model class must extend BaseModel.");
        }

        $collectionPages = $this->kirby->collection($collection);

        if (!isset($collectionPages)) {
            throw new KirbyRetrievalException('Collection ' . $collection . ' pages not found');
        }

        if ($filter && $filterFunction) {
            $modelList->setFilters($filter);
            $collectionPages = $filterFunction($collectionPages, $filter);
        }

        /** @var Page $collectionPage */
        foreach ($collectionPages as $collectionPage) {
            if ($setModelFunction) {
                $modelList->addListItem($setModelFunction($collectionPage->id()));
            } else {
                $modelList->addListItem($this->getModelPage($collectionPage->id(), $modelClass));
            }
        }

        return $modelList;
    }


    /**
     * @deprecated
     * @param string $pageId
     * @param class-string<BaseWebPage> $pageClass
     * @param callable|null $getPageFunction
     * @param string $collectionName
     * @return BaseWebPage
     */
    private function getSpecialisedPage(
        string   $pageId,
        string   $pageClass = BaseWebPage::class,
        ?callable $setPageFunction = null,
        string   $collectionName = '',
        ?callable $getPageFunction = null,
        bool     $checkUserRoles = true
    ): BaseWebPage
    {
        try {
            $kirbyPage = $this->getKirbyPage($pageId);
            $page = $this->getPage($kirbyPage, $pageClass, $checkUserRoles);
            if ($getPageFunction) {
                // Check if the created $page instance has the addListItem method
                if (!method_exists($page, 'addListItem')) {
                    throw new KirbyRetrievalException("The class {$pageClass} does not have an addListItem method.");
                }
                $collectionPages = $this->getPagesFromCollection($collectionName);


                foreach ($collectionPages as $collectionPage) {
                    if ($collectionPage instanceof Page) {
                        $listItem = $getPageFunction($collectionPage->id());
                        $page->addListItem($listItem);
                    }
                }
            }

            if (method_exists($this, 'setCurrentPage')) {
                $page = $this->setCurrentPage($kirbyPage, $page);
            }

            if ($setPageFunction) {
                $setPageFunction($kirbyPage, $page);
            }
        } catch (KirbyRetrievalException $e) {
            $page = $this->recordPageError($e, $pageClass);
        }
        return $page;
    }

    public function getSpecificPage(string $pageId, string $pageClass = BaseWebPage::class, $checkUserRoles = true) : BaseWebPage {
        try {
            $kirbyPage = $this->getKirbyPage($pageId);
            $page = $this->getPage($kirbyPage, $pageClass, $checkUserRoles);

            if (method_exists($this, 'setCurrentPage')) {
                $page = $this->setCurrentPage($kirbyPage, $page);
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
     * @param string $pageId
     * @param string $modelClass
     * @return BaseModel
     */
    public function getSpecificModel(string $pageId, string $modelClass) : BaseModel {
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
     * @return BaseList
     * @throws KirbyRetrievalException
     */
    private function getSpecificModelList(string          $modelListClass = BaseList::class,
                                          BaseFilter|null $filter = null,
                                          string|null     $collection = null,
                                          array|null      $templates = null
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
            $collectionPages = $this->getSubPagesUsingTemplates($this->page, $templates)->sortBy('title');
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

        /** @var Page $collectionPage */
        foreach ($collectionPages as $collectionPage) {
            $model = $this->getSpecificModel($collectionPage, $modelClass);
            $modelList->addListItem($model);
        }

        if ($modelList->usePagination()) {
            $paginationFromKirby = $collectionPages->pagination();

            if (isset($paginationFromKirby)) {
                $modelList->setPagination($this->getPagination($paginationFromKirby));
            }
        }

        return $modelList;
    }


    /**
     * @deprecated
     * @param string $pageId
     * @param class-string<BaseModel> $modelClass
     * @return BaseModel
     */
    private function getModelPage(
        string   $pageId,
        string   $modelClass = BaseModel::class,
        ?callable $setPageFunction = null,
    ): BaseModel
    {
        try {
            $kirbyPage = $this->getKirbyPage($pageId);
            if (!(is_a($modelClass, BaseModel::class, true))) {
                throw new KirbyRetrievalException("Page class must extend BaseModel.");
            }

            //TODO: using the WebPage constructor - will this always be right?
            $model = new $modelClass($kirbyPage->title()->toString(), $kirbyPage->url(), $kirbyPage->template()->name());

            if ($setPageFunction) {
                $model = $setPageFunction($kirbyPage, $model);
            }
        } catch (KirbyRetrievalException $e) {
            $model = $this->recordModelError($e, $modelClass);
        }
        return $model;
    }

    private function getEmptyWebPage(Page $kirbyPage, string $pageClass = BaseWebPage::class): BaseWebPage
    {
        $webPage = new $pageClass($kirbyPage->title()->toString(), $kirbyPage->url(), $kirbyPage->template()->name());
        $webPage->setPageId($kirbyPage->id());
        $user = $this->getCurrentUser();
        $webPage->setCurrentUser($user);
        return $webPage;
    }


    /**
     * @param KirbyRetrievalException $e
     * @param class-string<BaseWebPage> $pageClass
     * @return BaseWebPage
     */
    private function recordPageError(KirbyRetrievalException $e, string $pageClass = BaseWebPage::class): BaseWebPage
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
     */
    private function recordActionStatusError(ActionStatus $actionStatus, string $pageClass = BaseWebPage::class): BaseWebPage
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

    private function getErrorPage(string $pageClass = BaseWebPage::class) : BaseWebPage {
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
    private function recordModelError(KirbyRetrievalException $e, string $modelClass = BaseModel::class): BaseModel
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
     * @throws KirbyRetrievalException
     */
    private function sendEmail(string $template,
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
            } catch (Exception $error) {
                throw new KirbyRetrievalException(
                    'An error occurred when trying to send the email: '.$error->getMessage(),
                    $error->getCode()
                );
            }
    }

    /**
     * @param KirbyRetrievalException $e
     * @return void
     */
    private function sendErrorEmail(KirbyRetrievalException $e): void
    {
        $exceptionAsString =  "Message: " . $e->getMessage() . "\n" .
            "File:" . $e->getFile() . "'\n" .
            "Line:" . $e->getLine() . "\n" .
            "Trace:" . $e->getTraceAsString();
        error_log($exceptionAsString);
        if (!str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
            $this->kirby->email([
                'template' => 'error-notification',
                'from' => option('defaultEmail'),
                'replyTo' => option('defaultEmail'),
                'to' => option('adminEmail'),
                'subject' => 'Identiplant: Error',
                'data' => [
                    'errorMessage' => $this->getExceptionDetails($e),
                ]
            ]);
        }
    }

    private function getExceptionDetails(KirbyRetrievalException $exception): string
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

    private function getExceptionDetail(KirbyRetrievalException $exception): string
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
    private function getContentBlocks(Page $page, string $fieldName = 'mainContent'): WebPageBlocks
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
            return (new WebPageBlocks())->recordError($e->getMessage(), 'An error occurred while retrieving the page block');
        }
    }

    /**
     * @param string $pageId
     * @return string
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

    #region BREADCRUMB

    private function hasBreadcrumb(): bool
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
    private function getBreadcrumb(): WebPageLinks
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
        return $this->site->breadcrumb()->filterBy('template', '!=', 'home')->filterBy('isListed', true);
    }

    private function getUserNames(Page $page, string $fieldName): string
    {
        try {
            // Get the 'postedBy' field from the current page
            $postedByField = $page->content()->get($fieldName);

            // Check if the field is not empty
            if ($postedByField instanceof Field && $postedByField->isNotEmpty()) :
                // Get the users from the 'postedBy' field
                /** @noinspection PhpUndefinedMethodInspection */
                $users = $postedByField->toUsers();

                // Initialize an array to hold usernames
                $userNamesArray = [];

                // Loop through the users to get their names
                foreach ($users as $user) :
                    $userNamesArray[] = $user->name(); // Adjust the method to get the desired user attribute, e.g., name or username
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
    public function getSearchQuery(): string
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
     */
    public function search(string $query): WebPageLinks
    {
        return $this->getSearchResults($query);
    }

    /**
     * @param string $query
     * @param Collection|null $collection
     * @return WebPageLinks
     */
    private function getSearchResults(string $query, ?Collection $collection = null): WebPageLinks
    {
        $searchResults = new WebPageLinks();
        try {
            if ($collection === null) {
                $collection = $this->getSearchCollection($query);
            }

            $searchResults = $this->getWebPageLinks($collection);
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

        return $searchResults;
    }

    /**
     * @param \Kirby\Cms\Pagination $paginationFromKirby
     * @return Pagination
     */
    private function getPagination(\Kirby\Cms\Pagination $paginationFromKirby): Pagination
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
    private function getSearchCollection(
        ?string     $query = null,
        string     $params = 'title|mainContent|description',
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
    private function highlightSearchQuery(BaseWebPage $page, string $query): BaseWebPage
    {
        $mainContentBlocks = $page->getMainContent();
        foreach ($mainContentBlocks->getBlocks() as $block) {
            if (in_array($block->getBlockType(), ['text', 'heading', 'list', 'note'])) {
                $highlightedContent = $this->highlightTerm($block->getBlockContent(), $query);
                $block->setBlockContent($highlightedContent);
            }
        }
        return $page;
    }


    #endregion

    #region FILES
    /**
     * @param Page $page
     * @param string $fieldName
     * @return Document
     */
    private function getDocument(Page $page, string $fieldName): Document
    {
        try {
            $pageFile = $this->getPageFieldAsFile($page, $fieldName);
            if ($pageFile != null) {

                $url = $pageFile->url();
                /** @noinspection PhpUndefinedMethodInspection */
                $size = $pageFile->niceSize();
                $document = new Document('questionSheet',$url);
                $document->setSize($size);
                return $document;
            }
            return (new Document())->recordError('Document not found');
        } catch (KirbyRetrievalException) {
            return (new Document())->recordError('Document not found');
        }
    }

    private function getFileModifiedAsDateTime(File $file): DateTime {
        $modified = $file->modified();
        return (new DateTime())->setTimestamp($modified);

    }

    #endregion

    #region USERS

    private function getUserName(string $userId, string $fallback = 'User not found') : string {
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
    private function getCurrentUser(): User {
        $user = new User();

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
     * gets the current Kirby username - returns blank string if no user
     * @return string
     */
    private function getCurrentUserName(): string {
        return $this->kirby->user() ? $this->kirby->user()->name() : '';
    }

    /**
     * gets the current Kirby username - returns blank string if no user
     * @return string
     */
    private function getCurrentUserRole(): string {
        return $this->kirby->user() ? $this->kirby->user()->role()->name() : '';
    }

    #endregion

    #region TAGS

    /**
     * @param Collection $pages
     * @param string $tagName
     * @param string $tagValue
     * @return Collection
     */
    private function filterByPagesTag(Collection $pages, string $tagName, string $tagValue): Collection {
        if (empty($tagValue)) { return $pages;}
        return $pages->filter(function ($page) use ($tagName, $tagValue) {
            $pages = $page->content()->get($tagName)->toPages();
            return ($pages->filterBy('title', $tagValue)->count() > 0);
        });
    }

    /**
     * @param Page $kirbyPage
     * @param string $tagType
     * @param string $fieldName
     * @return WebPageTagLinkSet
     */
    private function getWebPageTagLinkSet(Page $kirbyPage, string $tagType, string $fieldName): WebPageTagLinkSet
    {
        $tagLinkSet = new WebPageTagLinkSet();
        $tagLinkSet->setTagType($tagType);
        $tagLinkSet->setLinks($this->getPageFieldAsWebPageTagLinks($kirbyPage,$fieldName));
        return $tagLinkSet;
    }


    #endregion

    #region MISC

    /**
     * gets the colour mode (getting/setting a colourMode cookie as required)
     * @return string
     * @throws KirbyRetrievalException
     */
    public function getColourMode(): string
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
    private function setCookie(string $key, string $value): void
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
     * @return string
     * @throws KirbyRetrievalException
     */
    private function getTurnstileSiteKey(): string {
        $turnstileSiteKey = (string) option('turnstile.siteKey');

        if (empty($turnstileSiteKey)) {
            throw new KirbyRetrievalException('The Turnstile sitekey for Uniform is not configured');
        }
        return $turnstileSiteKey;
    }

    /**
     * @throws KirbyRetrievalException
     */
    private function getTurnstileResponse(): void {

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


    /**
     * @param string $optionKey
     * @return string
     */
    public function getOption(string $optionKey): string
    {
        $optionValue = $this->kirby->option($optionKey);
        return $this->asString($optionValue);
    }

    private function asString(mixed $value): string
    {
        if (is_string($value)) {
            // The value is already a string
            $stringValue = $value;
        } else {
            // Handle non-string values
            $stringValue = $value !== null ? json_encode($value) : '';

            // Ensure json_encode did not return false
            if ($stringValue === false) {
                $stringValue = ''; // Default to an empty string if encoding fails
            }
        }
        return $stringValue;
    }


    /**
     * Get the default fallback datetime
     * where a date is required, but the overall
     * operation has failed.
     * @return DateTime
     */
    private function getFallBackDateTime(): DateTime
    {
        return new DateTime('1970-01-01 00:00:00');
    }

    function logCurrentTime(): void
    {
        $microtime = microtime(true);
        $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);
        $timestamp = (new DateTime())->setTimestamp((int)$microtime)->format("Y-m-d H:i:s");
        echo "[{$timestamp}.{$milliseconds}]<br>";
    }


    #endregion

}
