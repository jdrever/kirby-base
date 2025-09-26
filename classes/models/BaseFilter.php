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
}