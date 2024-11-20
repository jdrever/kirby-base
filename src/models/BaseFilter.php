<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class EventCategories
 * Represents a BSBI event category list with various properties and methods.
 *
 * @package BSBI\Web
 */
abstract class BaseFilter
{
    use ErrorHandling;

    /**
     * @param string $value
     * @param string $display
     * @return string[]
     */
    protected function createOption(string $value, string $display): array
    {
        return [
            'value' => $value,
            'display' => $display,
        ];

    }
}