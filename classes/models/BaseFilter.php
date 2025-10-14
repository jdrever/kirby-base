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


}