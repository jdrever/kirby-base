<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Detects and extracts raster images that have been embedded (base64-encoded)
 * inside SVG "wrapper" files.
 *
 * Some design tools export photographs as SVG by embedding the original
 * full-resolution bitmap as a base64 data URI. Such files carry the .svg
 * extension but are functionally large rasters: Kirby cannot resize an SVG, so
 * they bypass thumbnail generation and ship megabytes to the browser to render
 * a small icon. This service pulls the embedded bitmap back out so it can be
 * rasterised and thumbnailed normally.
 *
 * Pure logic only — no Kirby, filesystem or GD dependencies — so it is fully
 * unit testable.
 *
 * @package BSBI\WebBase
 */
final readonly class SvgRasterExtractor
{
    /**
     * Maps the image subtype found in a data URI to a file extension.
     *
     * SVG payloads are deliberately excluded: a nested SVG is not a raster and
     * must never be extracted.
     *
     * @var array<string, string>
     */
    private const array MIME_EXTENSIONS = [
        'png'  => 'png',
        'jpeg' => 'jpg',
        'jpg'  => 'jpg',
        'gif'  => 'gif',
        'webp' => 'webp',
    ];

    /**
     * Whether the given SVG markup contains at least one embedded base64 raster image.
     *
     * @param string $svgContents The raw SVG file contents.
     * @return bool True if a base64 raster data URI is present.
     */
    public function isRasterWrappedSvg(string $svgContents): bool
    {
        return preg_match('#data:image/(?:png|jpe?g|gif|webp);base64,#i', $svgContents) === 1;
    }

    /**
     * Extracts the largest embedded raster image from the SVG markup.
     *
     * When an SVG embeds several bitmaps (e.g. the artwork plus a shadow/mask
     * layer) the largest decoded payload is the meaningful image, so that is the
     * one returned.
     *
     * @param string $svgContents The raw SVG file contents.
     * @return ExtractedRaster|null The largest decoded raster, or null if none can be extracted.
     */
    public function extractLargestRaster(string $svgContents): ?ExtractedRaster
    {
        $matchCount = preg_match_all(
            '#data:image/([a-z0-9]+);base64,([A-Za-z0-9+/=\s]+)#i',
            $svgContents,
            $matches,
            PREG_SET_ORDER
        );

        if ($matchCount === false || $matchCount === 0) {
            return null;
        }

        $largest = null;
        foreach ($matches as $match) {
            $extension = self::MIME_EXTENSIONS[strtolower($match[1])] ?? null;
            if ($extension === null) {
                continue;
            }

            $bytes = base64_decode((string) preg_replace('/\s+/', '', $match[2]), true);
            if ($bytes === false || $bytes === '') {
                continue;
            }

            if ($largest === null || strlen($bytes) > $largest->size()) {
                $largest = new ExtractedRaster($bytes, $extension);
            }
        }

        return $largest;
    }
}
