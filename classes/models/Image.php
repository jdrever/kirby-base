<?php

namespace BSBI\WebBase\models;

/**
 * Class Image
 * Represents an image with responsive srcsets and performance attributes
 *
 * @package BSBI\WebBase
 */
class Image extends BaseModel
{

    /** @var string The src */
    private string $src;

    /** @var string The srcset */
    private string $srcset;

    /** @var string The webp srcset */
    private string $webpSrcset;

    /** @var string The AVIF srcset */
    private string $avifSrcset = '';

    /** @var string Single WebP URL for CSS background-image */
    private string $webpSrc = '';

    /** @var string Single AVIF URL for CSS background-image */
    private string $avifSrc = '';

    /** @var string The CSS class */
    private string $class;

    /** @var string The ALT text */
    private string $alt;

    /** @var int The width */
    private int $width;

    /** @var ?int The height */
    private ?int $height;

    private string $sizes = '';

    private string $caption = '';

    /** @var string Loading attribute (lazy|eager) - defaults to lazy for below-fold images */
    private string $loading = 'lazy';

    /** @var string Fetch priority (high|low|auto) - use high for LCP images */
    private string $fetchPriority = '';

    /** @var string Decoding attribute (async|sync|auto) - async prevents render blocking */
    private string $decoding = 'async';

    /**
     * @param string $src
     * @param string $srcset
     * @param string $webpSrcset
     * @param string $alt
     * @param int $width
     * @param ?int $height
     */
    public function __construct(string $src = '',
                                string $srcset = '',
                                string $webpSrcset = '',
                                string $alt = '',
                                int    $width = 0,
                                ?int    $height = 0)
    {
        $this->src = $src;
        $this->srcset = $srcset;
        $this->webpSrcset = $webpSrcset;
        $this->alt = $alt;
        $this->width = $width;
        $this->height = $height;
        parent::__construct('');
    }

    /**
     * @return string
     */
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * @param string $src
     * @return $this
     */
    public function setSrc(string $src): Image
    {
        $this->src = $src;
        return $this;
    }

    /**
     * @return string
     */
    public function getSrcset(): string
    {
        return $this->srcset;
    }

    /**
     * @param string $srcset
     * @return $this
     */
    public function setSrcset(string $srcset): Image
    {
        $this->srcset = $srcset;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebpSrcset(): string
    {
        return $this->webpSrcset;
    }

    /**
     * @param string $webpSrcset
     * @return $this
     */
    public function setWebpSrcset(string $webpSrcset): Image
    {
        $this->webpSrcset = $webpSrcset;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAvifSrcset(): bool
    {
        return !empty($this->avifSrcset);
    }

    /**
     * @return string
     */
    public function getAvifSrcset(): string
    {
        return $this->avifSrcset;
    }

    /**
     * @param string $avifSrcset
     * @return $this
     */
    public function setAvifSrcset(string $avifSrcset): Image
    {
        $this->avifSrcset = $avifSrcset;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasWebpSrc(): bool
    {
        return !empty($this->webpSrc);
    }

    /**
     * Get single WebP URL for use in CSS background-image
     * @return string
     */
    public function getWebpSrc(): string
    {
        return $this->webpSrc;
    }

    /**
     * @param string $webpSrc
     * @return $this
     */
    public function setWebpSrc(string $webpSrc): Image
    {
        $this->webpSrc = $webpSrc;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAvifSrc(): bool
    {
        return !empty($this->avifSrc);
    }

    /**
     * Get single AVIF URL for use in CSS background-image
     * @return string
     */
    public function getAvifSrc(): string
    {
        return $this->avifSrc;
    }

    /**
     * @param string $avifSrc
     * @return $this
     */
    public function setAvifSrc(string $avifSrc): Image
    {
        $this->avifSrc = $avifSrc;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoading(): string
    {
        return $this->loading;
    }

    /**
     * @param string $loading (lazy|eager)
     * @return $this
     */
    public function setLoading(string $loading): Image
    {
        $this->loading = $loading;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasFetchPriority(): bool
    {
        return !empty($this->fetchPriority);
    }

    /**
     * @return string
     */
    public function getFetchPriority(): string
    {
        return $this->fetchPriority;
    }

    /**
     * @param string $fetchPriority (high|low|auto)
     * @return $this
     */
    public function setFetchPriority(string $fetchPriority): Image
    {
        $this->fetchPriority = $fetchPriority;
        return $this;
    }

    /**
     * @return string
     */
    public function getDecoding(): string
    {
        return $this->decoding;
    }

    /**
     * @param string $decoding (async|sync|auto)
     * @return $this
     */
    public function setDecoding(string $decoding): Image
    {
        $this->decoding = $decoding;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasClass(): bool
    {
        return !empty($this->class);
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass(string $class): Image
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlt(): string
    {
        return $this->alt;
    }

    /**
     * @param string $alt
     * @return $this
     */
    public function setAlt(string $alt): Image
    {
        $this->alt = $alt;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth(int $width): Image
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height): Image
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !empty($this->src);
    }

    /**
     * @return bool
     */
    public function hasSizes(): bool {
        return !empty($this->sizes);
    }

    /**
     * @return string
     */
    public function getSizes(): string
    {
        return $this->sizes;
    }

    /**
     * @param string $sizes
     * @return Image
     */
    public function setSizes(string $sizes): Image
    {
        $this->sizes = $sizes;
        return $this;
    }

    public function hasCaption(): bool
    {
        return !empty($this->caption);
    }


    /**
     * @return string
     */
    public function getCaption(): string
    {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getCaptionWithoutHTML(): string
    {
        return strip_tags($this->caption);
    }

    /**
     * @param string $caption
     * @return Image
     */
    public function setCaption(string $caption): Image
    {
        $this->caption = $caption;
        return $this;
    }


}
