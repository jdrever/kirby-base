<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\Image;

/**
 * Trait ImageHandling
 * To add an image to a model
 *
 * @package BSBI\traits
 */
trait ImageHandling
{
    /** @var Image The image */
    private Image $image;

    public function setImage(Image $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function hasImage(): bool {
        return isset($this->image);
    }

    public function getImage(): Image
    {
        return $this->image;
    }

}
