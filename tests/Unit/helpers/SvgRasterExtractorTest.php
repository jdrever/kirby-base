<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\ExtractedRaster;
use BSBI\WebBase\helpers\SvgRasterExtractor;
use PHPUnit\Framework\TestCase;

/**
 * Tests for SvgRasterExtractor — pure logic, no Kirby/filesystem/GD.
 *
 * Covers detection of base64-embedded rasters inside SVG wrappers and
 * extraction of the largest embedded bitmap (the meaningful artwork when an
 * exporter also embeds a shadow/mask layer).
 */
final class SvgRasterExtractorTest extends TestCase
{
    private SvgRasterExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new SvgRasterExtractor();
    }

    /**
     * Builds an SVG whose <image> elements embed the given binary payloads.
     *
     * @param array<array{0: string, 1: string}> $rasters List of [mime, binary] pairs.
     * @return string
     */
    private function svgWithRasters(array $rasters): string
    {
        $images = '';
        foreach ($rasters as [$mime, $binary]) {
            $images .= '<image width="10" height="10" xlink:href="data:' . $mime
                . ';base64,' . base64_encode($binary) . '"/>';
        }
        return '<svg xmlns="http://www.w3.org/2000/svg">' . $images . '</svg>';
    }

    // -------------------------------------------------------------------------
    // isRasterWrappedSvg
    // -------------------------------------------------------------------------

    public function testDetectsEmbeddedPngRaster(): void
    {
        $svg = $this->svgWithRasters([['image/png', 'PNGDATA']]);
        $this->assertTrue($this->extractor->isRasterWrappedSvg($svg));
    }

    public function testDetectsEmbeddedJpegRaster(): void
    {
        $svg = $this->svgWithRasters([['image/jpeg', 'JPEGDATA']]);
        $this->assertTrue($this->extractor->isRasterWrappedSvg($svg));
    }

    public function testGenuineVectorSvgIsNotRasterWrapped(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0 L10 10"/></svg>';
        $this->assertFalse($this->extractor->isRasterWrappedSvg($svg));
    }

    public function testEmptyStringIsNotRasterWrapped(): void
    {
        $this->assertFalse($this->extractor->isRasterWrappedSvg(''));
    }

    // -------------------------------------------------------------------------
    // extractLargestRaster
    // -------------------------------------------------------------------------

    public function testReturnsNullWhenNoRasterPresent(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle r="5"/></svg>';
        $this->assertNull($this->extractor->extractLargestRaster($svg));
    }

    public function testExtractsSingleRasterBytes(): void
    {
        $svg     = $this->svgWithRasters([['image/png', 'HELLO-PNG-BYTES']]);
        $raster  = $this->extractor->extractLargestRaster($svg);

        $this->assertInstanceOf(ExtractedRaster::class, $raster);
        $this->assertSame('HELLO-PNG-BYTES', $raster->bytes);
        $this->assertSame('png', $raster->extension);
    }

    public function testPicksLargestOfSeveralRasters(): void
    {
        // Mimics an exporter embedding a small mask plus the full-res image.
        $small = 'tiny';
        $large = str_repeat('X', 5000);
        $svg   = $this->svgWithRasters([
            ['image/png', $small],
            ['image/png', $large],
        ]);

        $raster = $this->extractor->extractLargestRaster($svg);

        $this->assertInstanceOf(ExtractedRaster::class, $raster);
        $this->assertSame($large, $raster->bytes);
    }

    public function testPicksLargestRegardlessOfOrder(): void
    {
        $large = str_repeat('Y', 5000);
        $small = 'tiny';
        $svg   = $this->svgWithRasters([
            ['image/png', $large],
            ['image/png', $small],
        ]);

        $raster = $this->extractor->extractLargestRaster($svg);
        $this->assertSame($large, $raster->bytes);
    }

    public function testJpegMimeMapsToJpgExtension(): void
    {
        $svg    = $this->svgWithRasters([['image/jpeg', 'JPEG-BYTES']]);
        $raster = $this->extractor->extractLargestRaster($svg);

        $this->assertSame('jpg', $raster->extension);
    }

    public function testHandlesBase64WithWhitespaceLineBreaks(): void
    {
        $binary  = str_repeat('Z', 200);
        $encoded = chunk_split(base64_encode($binary));
        $svg     = '<svg><image xlink:href="data:image/png;base64,' . $encoded . '"/></svg>';

        $raster = $this->extractor->extractLargestRaster($svg);

        $this->assertInstanceOf(ExtractedRaster::class, $raster);
        $this->assertSame($binary, $raster->bytes);
    }

    public function testIgnoresEmbeddedSvgDataUri(): void
    {
        // A nested SVG data URI is not a raster and must not be extracted.
        $svg = '<svg><image xlink:href="data:image/svg+xml;base64,'
            . base64_encode('<svg></svg>') . '"/></svg>';

        $this->assertFalse($this->extractor->isRasterWrappedSvg($svg));
        $this->assertNull($this->extractor->extractLargestRaster($svg));
    }

    public function testExtractedRasterReportsSize(): void
    {
        $raster = new ExtractedRaster('abcde', 'png');
        $this->assertSame(5, $raster->size());
    }
}
