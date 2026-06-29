<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\ImageService;
use BSBI\WebBase\helpers\KirbyFieldReader;
use BSBI\WebBase\models\ImageType;
use FilesystemIterator;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Filesystem\Asset;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Integration tests for ImageService's raster-wrapped SVG handling.
 *
 * Uses real on-disk files and a booted Kirby App so the thumbnail/Asset
 * pipeline runs for real: a raster-wrapped SVG must be replaced by a resizable
 * Asset, while genuine vector SVGs, small SVGs and ordinary rasters are
 * untouched.
 */
final class ImageServiceRasterSvgTest extends TestCase
{
    private static App $kirby;
    private static ImageService $service;
    private static string $root;

    public static function setUpBeforeClass(): void
    {
        self::$root = sys_get_temp_dir() . '/kirby-raster-svg-' . uniqid();
        $content    = self::$root . '/content';
        $iconsDir   = $content . '/icons';
        mkdir($iconsDir, 0777, true);
        file_put_contents($content . '/site.txt', "Title: Test Site\n");
        file_put_contents($iconsDir . '/default.txt', "Title: Icons\n");

        // A real PNG, large enough that the wrapping SVG exceeds the 100KB threshold.
        $png = self::makeNoisePng(400, 400);

        // Raster-wrapped SVG: a tiny vector shell around an embedded full-res PNG.
        file_put_contents(
            $iconsDir . '/raster-icon.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '
            . 'width="378" height="378"><image width="400" height="400" '
            . 'xlink:href="data:image/png;base64,' . base64_encode($png) . '"/></svg>'
        );

        // Genuine vector SVG.
        file_put_contents(
            $iconsDir . '/vector-icon.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0 L10 10 Z"/></svg>'
        );

        // A raster-wrapped SVG that is below the size threshold.
        file_put_contents(
            $iconsDir . '/small-raster.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><image xlink:href="data:image/png;base64,'
            . base64_encode('tiny') . '"/></svg>'
        );

        // An ordinary raster file.
        file_put_contents($iconsDir . '/photo.png', $png);

        self::$kirby = new App([
            'roots' => [
                'index'   => self::$root,
                'content' => $content,
                'media'   => self::$root . '/media',
            ],
            'options' => [
                'thumbs' => [
                    'srcsets' => [
                        'thumbnail'      => [300 => ['width' => 300]],
                        'thumbnail-webp' => [300 => ['width' => 300, 'format' => 'webp']],
                    ],
                ],
            ],
        ]);

        $fieldReader   = new KirbyFieldReader(self::$kirby, self::$kirby->site());
        self::$service = new ImageService($fieldReader);
    }

    public static function tearDownAfterClass(): void
    {
        self::removeDir(self::$root);
    }

    /**
     * Generates a real noise PNG (noise resists compression so the file is large).
     *
     * @param int $width
     * @param int $height
     * @return string The PNG binary.
     */
    private static function makeNoisePng(int $width, int $height): string
    {
        $img = imagecreatetruecolor($width, $height);
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                imagesetpixel($img, $x, $y, random_int(0, 0xFFFFFF));
            }
        }
        ob_start();
        imagepng($img, null, 0);
        $data = (string) ob_get_clean();
        imagedestroy($img);
        return $data;
    }

    private static function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        /** @var SplFileInfo $item */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    private function file(string $filename): File
    {
        return self::$kirby->page('icons')->file($filename);
    }

    // -------------------------------------------------------------------------
    // resolveRasterSource
    // -------------------------------------------------------------------------

    public function testRasterWrappedSvgIsReplacedWithResizableAsset(): void
    {
        $source = self::$service->resolveRasterSource($this->file('raster-icon.svg'));

        $this->assertInstanceOf(Asset::class, $source);
        $this->assertSame('png', strtolower($source->extension()));
        $this->assertFileExists($source->root());
        $this->assertTrue($source->isResizable());
    }

    public function testVectorSvgIsLeftUnchanged(): void
    {
        $file = $this->file('vector-icon.svg');
        $this->assertSame($file, self::$service->resolveRasterSource($file));
    }

    public function testSmallRasterWrappedSvgIsLeftUnchanged(): void
    {
        $file = $this->file('small-raster.svg');
        $this->assertSame($file, self::$service->resolveRasterSource($file));
    }

    public function testNonSvgFileIsLeftUnchanged(): void
    {
        $file = $this->file('photo.png');
        $this->assertSame($file, self::$service->resolveRasterSource($file));
    }

    public function testRasterisedAssetIsCachedAndReused(): void
    {
        $file   = $this->file('raster-icon.svg');
        $first  = self::$service->resolveRasterSource($file);
        $second = self::$service->resolveRasterSource($file);

        $this->assertInstanceOf(Asset::class, $second);
        $this->assertSame($first->root(), $second->root());
    }

    // -------------------------------------------------------------------------
    // getImageFromFile end-to-end
    // -------------------------------------------------------------------------

    public function testGetImageFromFileRasterisesWrappedSvg(): void
    {
        $image = self::$service->getImageFromFile(
            $this->file('raster-icon.svg'),
            300,
            300,
            90,
            ImageType::THUMBNAIL
        );

        $this->assertTrue($image->isAvailable());
        $this->assertFalse($image->hasErrors());
        $this->assertStringEndsNotWith('.svg', $image->getSrc());
        $this->assertStringContainsString('.webp', $image->getWebpSrc());
    }
}
