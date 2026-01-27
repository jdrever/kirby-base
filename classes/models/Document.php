<?php

namespace BSBI\WebBase\models;

use DateTime;

/**
 * Represents a document
 *
 * @package BSBI\Web
 */
class Document extends BaseModel
{
    private string $size;

    private DateTime $modified;

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size ?? 'Unknown';
    }

    /**
     * @param string $size
     * @return void
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    /**
     * @return DateTime
     */
    public function getModifiedDate(): DateTime
    {
        return $this->modified;
    }

    /**
     * @param DateTime $modified
     * @return $this
     */
    public function setModifiedDate(DateTime $modified): Document
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormattedModifiedDate(): string {
        return $this->modified->format('d/m/Y H:i:s');
    }

}
