<?php

namespace BSBI\WebBase\models;

/**
 * Class Person
 * Represents a person (e.g. member of staff or trustee)
 *
 * @package BSBI\Web
 */
class Image extends BaseModel
{

    /** @var string The src */
    private string $src;

    /** @var string The srcset */
    private string $srcset;

    /** @var string The webp srcset */
    private string $webpSrcset;

    /** @var string The CSS class */
    private string $class;

    /** @var string The ALT text */
    private string $alt;

    /** @var int The width */
    private int $width;

    /** @var int The height */
    private int $height;

    private string $sizes = '';

    /**
     * @param string $src
     * @param string $srcset
     * @param string $webpSrcset
     * @param string $alt
     * @param int $width
     * @param int $height
     */
    public function __construct(string $src = '',
                                string $srcset = '',
                                string $webpSrcset = '',
                                string $alt = '',
                                int    $width = 0,
                                int    $height = 0)
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



}
