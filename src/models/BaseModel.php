<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class BaseModel
 * Basic model class
 *
 * @package BSBI\Web
 */
abstract class BaseModel
{
    use ErrorHandling;

    /** @var string The URL associated with the model */
    protected string $url = '';

    /** @var string The title of the model */
    protected string $title = '';

    /**
     * Constructor.
     *
     * @param string $title The title of the model
     * @param string $url The URL of the model
     */
    public function __construct(string $title, string $url = '')
    {
        $this->title = $title;
        $this->url = $url;
        $this->status = true;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasUrl(): bool {
        return (!empty($this->url));
    }

    /**
     * Get the value of url
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the value of url
     * @param string $url
     * @return BaseModel
     * @noinspection PhpUnused
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the value of title
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

}
