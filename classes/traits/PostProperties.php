<?php

namespace BSBI\WebBase\traits;

use DateTime;

/**
 *
 */
trait PostProperties
{
    /** @var DateTime */
    private DateTime $publicationDate;

    private string $subtitle;

    /**
     * @var string
     */
    private string $excerpt;

    private string $postedBy;

    public function hasSubtitle(): bool
    {
        return !empty($this->subtitle);
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     * @return $this
     */
    public function setSubtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getExcerpt(): string
    {
        return $this->excerpt ?? '';
    }

    /**
     * @param string $excerpt
     * @return $this
     */
    public function setExcerpt(string $excerpt): static
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPostedBy(): bool
    {
        return !empty($this->postedBy) && ($this->postedBy !== 'Unknown');
    }

    /**
     * @return string
     */
    public function getPostedBy(): string
    {
        return $this->postedBy;
    }

    /**
     * @param string $postedBy
     * @return $this
     */
    public function setPostedBy(string $postedBy): static
    {
        $this->postedBy = $postedBy;
        return $this;
    }

    public function hasPublicationDate(): bool {
        return isset($this->publicationDate);
    }

    /**
     * @param DateTime $publicationDate
     * @return $this
     */
    public function setPublicationDate(DateTime $publicationDate): static
    {
        $this->publicationDate = $publicationDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPublicationDate(): DateTime
    {
        return $this->publicationDate;
    }

    /**
     * Get the date in 'jS F Y' format
     * @return string
     */
    public function getFormattedPublicationDate(): string {
        return $this->publicationDate->format('jS F Y');
    }

}