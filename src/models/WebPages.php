<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ListHandling;
use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class ViceCounties
 * Represents a list of web pages with various properties and methods.
 * @package BSBI\Web
 */
class WebPages
{
    /**
     * @use ListHandling<\BSBI\WebBase\models\WebPage>
     */
    use ListHandling;
    use ErrorHandling;
    /**
     * Add a web page
     * @param \BSBI\WebBase\models\WebPage $webPage
     */
    public function addListItem(WebPage $webPage): void
    {
        $this->add($webPage);
    }

    /**
     * @return \BSBI\WebBase\models\WebPage[]
     */
    public function getPages(): array {
        return $this->list;
    }

}
