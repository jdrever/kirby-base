<?php

namespace BSBI\WebBase\models;

/**
 * Class RelatedContent
 * Represents related content for a web page
 *
 * @package BSBI\Web
 */
class RelatedContent extends BaseModel
{

    /**
     * @var bool
     */
    private bool $openInNewTab;

    /**
     * @param string $title
     * @param string $url
     * @param bool $openInNewTab
     */
    public function __construct(string $title,
                                string $url,
                                bool   $openInNewTab = false)
    {
        $this->openInNewTab = $openInNewTab;
        parent::__construct($title, $url);
    }

    /**
     * @return bool
     */
    public function openInNewTab(): bool
    {
        return $this->openInNewTab;
    }




}
