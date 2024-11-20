<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ListHandling;

/**
 * Class CoreLinks
 * Represents a BSBI core link (e.g. Contact Page) with various properties and methods.
 * @package BSBI\Web
 */
class CoreLinks
{
    /**
     * @use ListHandling<CoreLink,BaseFilter>
     */
    use ListHandling;

    /**
     * Add a link
     * @param CoreLink $link
     */
    public function addListItem(CoreLink $link): void
    {
        $this->add($link);
    }

    /**
     * Find the page with the matching link type
     * @param string $linkType
     * @return CoreLink
     */
    public function getPage(string $linkType): CoreLink
    {
        $matchingPage = array_filter($this->list, function ($item) use ($linkType) {
            if ($item instanceof CoreLink) {
                return $item->getType() === $linkType;
            }
            return false;
        });

        if (count($matchingPage) === 0) {
            $linkNotFound = new CoreLink('','', 'NOT_FOUND');
            $linkNotFound->setStatus(false);
            return $linkNotFound;
        }
        return reset($matchingPage);
    }
}
