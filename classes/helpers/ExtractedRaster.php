<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Immutable value object describing a single raster image that was extracted
 * from the base64 payload of an SVG wrapper file.
 *
 * @package BSBI\WebBase
 */
final readonly class ExtractedRaster
{
    /**
     * @param string $bytes The decoded binary image data.
     * @param string $extension The file extension implied by the source data URI (e.g. 'png', 'jpg').
     */
    public function __construct(
        public string $bytes,
        public string $extension
    ) {
    }

    /**
     * The size of the decoded image in bytes.
     *
     * @return int
     */
    public function size(): int
    {
        return strlen($this->bytes);
    }
}
