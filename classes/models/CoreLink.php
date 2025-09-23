<?php

namespace BSBI\WebBase\models;

/**
 * Represents a core link for a web page
 *
 * @package BSBI\Web
 */
class CoreLink extends BaseModel
{

    /**
     * @var string
     */
    private string $coreWebPageLinkType;

    /**
     * @param string $title
     * @param string $url
     * @param string $coreWebPageLinkType
     */
    public function __construct(string       $title,
                                string       $url,
                                string $coreWebPageLinkType)
    {
        $this->coreWebPageLinkType = $coreWebPageLinkType;
        parent::__construct($title, $url);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->coreWebPageLinkType;
    }


}
