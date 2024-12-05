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

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    public function getModifiedDate(): DateTime
    {
        return $this->modified;
    }

    public function setModifiedDate(DateTime $modified): Document
    {
        $this->modified = $modified;
        return $this;
    }

    public function getFormattedModifiedDate(): string {
        return $this->modified->format('d/m/Y H:i:s');
    }

}
