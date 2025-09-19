<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

use BSBI\WebBase\models\BaseWebPage;
use Kirby\Cms\Page;


/**
 * Intended for use within the plugin,
 * e.g. by the plugin controllers.
 */
class KirbyInternalHelper extends KirbyBaseHelper
{
    /**
     * @throws KirbyRetrievalException
     */
    function getBasicPage(): BaseWebPage
    {
        throw new KirbyRetrievalException('Not implemented');
    }

    /**
     * @param Page $kirbyPage
     * @param BaseWebPage $currentPage
     * @return BaseWebPage
     * @throws KirbyRetrievalException
     */
    function setBasicPage(Page $kirbyPage, BaseWebPage $currentPage): BaseWebPage
    {
        throw new KirbyRetrievalException('Not implemented');
    }
}