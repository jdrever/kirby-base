<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\ImageConversionHelper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests for ImageConversionHelper: BMP to PNG conversion.
 */
final class ImageConversionHelperTest extends TestCase
{
    private string $tmpBmpPath = '';

    protected function setUp(): void
    {
        $this->tmpBmpPath = sys_get_temp_dir() . '/test_' . uniqid('', true) . '.bmp';
        file_put_contents($this->tmpBmpPath, $this->minimalBmpBytes());
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpBmpPath)) {
            unlink($this->tmpBmpPath);
        }
    }

    // ── canConvertBmp ─────────────────────────────────────────────────────────

    public function testCanConvertBmpReturnsBool(): void
    {
        $this->assertIsBool(ImageConversionHelper::canConvertBmp());
    }

    public function testCanConvertBmpReturnsTrueWhenGdAvailable(): void
    {
        if (!function_exists('imagecreatefrombmp')) {
            $this->markTestSkipped('ext-gd not available');
        }

        $this->assertTrue(ImageConversionHelper::canConvertBmp());
    }

    public function testCanConvertBmpReturnsTrueWhenImagickAvailable(): void
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('ext-imagick not available');
        }

        $this->assertTrue(ImageConversionHelper::canConvertBmp());
    }

    // ── convertBmpToPng ───────────────────────────────────────────────────────

    public function testConvertBmpToPngProducesValidPngFile(): void
    {
        if (!ImageConversionHelper::canConvertBmp()) {
            $this->markTestSkipped('Neither Imagick nor GD available');
        }

        $pngPath = ImageConversionHelper::convertBmpToPng($this->tmpBmpPath);

        try {
            $this->assertFileExists($pngPath);
            $this->assertStringEndsWith('.png', $pngPath);

            // Verify the 8-byte PNG signature
            $header = file_get_contents($pngPath, false, null, 0, 8);
            $this->assertSame("\x89PNG\r\n\x1a\n", $header, 'Output file must have valid PNG signature');
        } finally {
            if (file_exists($pngPath)) {
                unlink($pngPath);
            }
        }
    }

    public function testConvertBmpToPngReturnsPathInTmpDir(): void
    {
        if (!ImageConversionHelper::canConvertBmp()) {
            $this->markTestSkipped('Neither Imagick nor GD available');
        }

        $pngPath = ImageConversionHelper::convertBmpToPng($this->tmpBmpPath);

        try {
            $this->assertStringStartsWith(sys_get_temp_dir(), $pngPath);
        } finally {
            if (file_exists($pngPath)) {
                unlink($pngPath);
            }
        }
    }

    public function testConvertBmpToPngThrowsWhenNoLibraryAvailable(): void
    {
        if (ImageConversionHelper::canConvertBmp()) {
            $this->markTestSkipped('Imagick or GD available — cannot test the no-library path');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/No image library available/');

        ImageConversionHelper::convertBmpToPng($this->tmpBmpPath);
    }

    public function testConvertBmpToPngThrowsForNonExistentFile(): void
    {
        if (!ImageConversionHelper::canConvertBmp()) {
            $this->markTestSkipped('Neither Imagick nor GD available');
        }

        $this->expectException(\Throwable::class);

        ImageConversionHelper::convertBmpToPng('/nonexistent/path/fake.bmp');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Returns the binary content of a minimal valid 1×1 white 24-bit BMP file.
     *
     * Structure: 14-byte file header + 40-byte BITMAPINFOHEADER + 4 bytes pixel data = 58 bytes.
     */
    private function minimalBmpBytes(): string
    {
        return pack(
            'H*',
            '424d' .       // BM signature
            '3a000000' .   // file size: 58 bytes
            '00000000' .   // reserved
            '36000000' .   // pixel data offset: 54
            '28000000' .   // DIB header size: 40
            '01000000' .   // width: 1 px
            '01000000' .   // height: 1 px
            '0100' .       // colour planes: 1
            '1800' .       // bits per pixel: 24
            '00000000' .   // compression: none
            '04000000' .   // image data size: 4 bytes
            '130b0000' .   // horizontal resolution (2835 px/m)
            '130b0000' .   // vertical resolution
            '00000000' .   // colours in table
            '00000000' .   // important colours
            'ffffff00'     // 1 white pixel (RGB) + 1 padding byte
        );
    }
}
