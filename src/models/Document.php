<?php

namespace BSBI\WebBase\models;

/**
 * Represents a document
 *
 * @package BSBI\Web
 */
class Document extends BaseModel
{
    private string $size;

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): void
    {
        $this->size = $size;
    }



}
