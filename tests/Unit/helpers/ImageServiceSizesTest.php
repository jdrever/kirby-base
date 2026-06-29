<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\ImageService;
use BSBI\WebBase\helpers\KirbyFieldReader;
use BSBI\WebBase\models\ImageSizes;
use BSBI\WebBase\models\ImageType;
use Kirby\Cms\App;
use Kirby\Cms\File;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that ImageService stamps the responsive `sizes` hint onto the
 * generated Image from the ImageSizes argument it is given.
 *
 * This is the single seam through which every responsive image (including the
 * home-page "what's happening"/"get involved" cards built by
 * KirbyBaseHelper::getStructureAsWebPageLinks) acquires its `sizes` attribute,
 * so getting the right ImageSizes here is what lets the browser pick a
 * correctly-sized srcset candidate (PageSpeed "image larger than needed").
 *
 * Uses a real raster image so thumb()/srcset() run for real; skipped when the
 * GD/WebP toolchain is unavailable.
 */
final class ImageServiceSizesTest extends TestCase
{
    private static App $kirby;
    private static ImageService $service;
    private static string $tmpDir;
    private static ?File $imageFile = null;

    public static function setUpBeforeClass(): void
    {
        if (!function_exists('imagepng') || !function_exists('imagewebp')) {
            return; // each test markTestSkipped()s below
        }

        self::$tmpDir = sys_get_temp_dir() . '/kirby-image-sizes-test-' . uniqid();
        $contentDir   = self::$tmpDir . '/content';
        $pageDir      = $contentDir . '/photos';
        mkdir($pageDir, 0777, true);

        file_put_contents($contentDir . '/site.txt', "Title: Test Site\n");
        file_put_contents($pageDir . '/photos.txt', "Title: Photos\n");

        // A real PNG large enough to feed the panel srcset variants.
        $im = imagecreatetruecolor(900, 675);
        imagepng($im, $pageDir . '/photo.png');
        imagedestroy($im);

        self::$kirby = new App([
            'roots'   => [
                'index'   => self::$tmpDir,
                'content' => $contentDir,
            ],
            'options' => [
                'thumbs' => [
                    'quality' => 80,
                    'srcsets' => [
                        'panel' => [
                            '400w' => ['width' => 400, 'height' => 300, 'crop' => true],
                            '800w' => ['width' => 800, 'height' => 600, 'crop' => true],
                        ],
                        'panel-webp' => [
                            '400w' => ['width' => 400, 'height' => 300, 'format' => 'webp', 'crop' => true],
                            '800w' => ['width' => 800, 'height' => 600, 'format' => 'webp', 'crop' => true],
                        ],
                    ],
                ],
            ],
        ]);

        $fieldReader   = new KirbyFieldReader(self::$kirby, self::$kirby->site());
        self::$service = new ImageService($fieldReader);
        self::$imageFile = self::$kirby->page('photos')?->file('photo.png');
    }

    public static function tearDownAfterClass(): void
    {
        if (isset(self::$tmpDir) && is_dir(self::$tmpDir)) {
            exec('rm -rf ' . escapeshellarg(self::$tmpDir));
        }
    }

    private function requireImage(): File
    {
        if (self::$imageFile === null) {
            $this->markTestSkipped('GD/WebP toolchain unavailable for real thumbnail generation');
        }
        return self::$imageFile;
    }

    public function testAppliesExplicitImageSizesToGeneratedImage(): void
    {
        $image = self::$service->getImageFromFile(
            $this->requireImage(),
            400,
            300,
            80,
            ImageType::PANEL,
            '',
            ImageSizes::THIRD_LARGE_SCREEN
        );

        $this->assertTrue($image->isAvailable());
        $this->assertSame(ImageSizes::THIRD_LARGE_SCREEN->value, $image->getSizes());
        $this->assertTrue($image->hasSizes());
    }

    public function testDefaultsToNoSizesWhenNotSpecified(): void
    {
        $image = self::$service->getImageFromFile(
            $this->requireImage(),
            400,
            300,
            80,
            ImageType::PANEL
        );

        $this->assertSame('', $image->getSizes());
        $this->assertFalse($image->hasSizes());
    }
}
