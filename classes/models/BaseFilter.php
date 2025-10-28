<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ErrorHandling;
use BSBI\WebBase\traits\OptionsHandling;

/**
 * Class EventCategories
 * Represents a BSBI event category list with various properties and methods.
 *
 * @package BSBI\Web
 */
abstract class BaseFilter
{
    use ErrorHandling;
    use OptionsHandling;

    /** @var string[] */
    private array $description;

    private string $keywords = '';

    private bool $stopPagination = false;


    public function hasDescription(): bool
    {
        return isset($this->description) && count($this->description)>0;
    }

    /**
     * @return string []
     */
    public function getDescription(): array
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return BaseFilter
     */
    public function addToDescription(string $description): BaseFilter
    {
        $this->description [] = $description;
        return $this;
    }

    public function hasKeywords(): bool
    {
        return !empty($this->keywords);
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     * @return BaseFilter
     */
    public function setKeywords(string $keywords): BaseFilter
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return bool
     */
    public function doStopPagination(): bool
    {
        return $this->stopPagination;
    }

    /**
     * @param bool $stopPagination
     * @return BaseFilter
     */
    public function setStopPagination(bool $stopPagination): BaseFilter
    {
        $this->stopPagination = $stopPagination;
        return $this;
    }


}