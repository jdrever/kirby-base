<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Image;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Image model.
 *
 * Covers constructor defaults, availability checks, srcset/WebP/AVIF variants,
 * caption (with and without HTML), sizes, CSS class, loading/decoding strategies,
 * and fetch priority.
 */
final class ImageTest extends TestCase
{
    /**
     * Create an Image with sensible defaults for testing.
     *
     * @param string $src    The image source URL
     * @param string $alt    The alt text
     * @param int    $width  The image width in pixels
     * @param int    $height The image height in pixels
     * @return Image
     */
    private function createImage(
        string $src = '/img/photo.jpg',
        string $alt = 'A photo',
        int $width = 800,
        int $height = 600,
    ): Image {
        return new Image($src, '', '', $alt, $width, $height);
    }

    // --- Constructor defaults ---

    /**
     * Verify the constructor correctly assigns src, alt, width and height.
     */
    public function testConstructorSetsBasicProperties(): void
    {
        $image = $this->createImage();

        $this->assertSame('/img/photo.jpg', $image->getSrc());
        $this->assertSame('A photo', $image->getAlt());
        $this->assertSame(800, $image->getWidth());
        $this->assertSame(600, $image->getHeight());
    }

    /**
     * Verify default loading strategy is 'lazy' and decoding is 'async'.
     */
    public function testDefaultLoadingAndDecoding(): void
    {
        $image = $this->createImage();

        $this->assertSame('lazy', $image->getLoading());
        $this->assertSame('async', $image->getDecoding());
    }

    // --- isAvailable ---

    /**
     * Verify isAvailable() returns true when a source URL is set.
     */
    public function testIsAvailableWhenSrcIsSet(): void
    {
        $image = $this->createImage();
        $this->assertTrue($image->isAvailable());
    }

    /**
     * Verify isAvailable() returns false when no source URL is provided.
     */
    public function testIsNotAvailableWhenSrcIsEmpty(): void
    {
        $image = new Image();
        $this->assertFalse($image->isAvailable());
    }

    // --- Srcset variants ---

    /**
     * Verify srcset can be set and retrieved.
     */
    public function testSrcsetGetterSetter(): void
    {
        $image = $this->createImage();
        $image->setSrcset('photo-320.jpg 320w, photo-640.jpg 640w');

        $this->assertSame('photo-320.jpg 320w, photo-640.jpg 640w', $image->getSrcset());
    }

    /**
     * Verify WebP srcset can be set and retrieved.
     */
    public function testWebpSrcsetGetterSetter(): void
    {
        $image = $this->createImage();
        $image->setWebpSrcset('photo.webp 800w');

        $this->assertSame('photo.webp 800w', $image->getWebpSrcset());
    }

    /**
     * Verify AVIF srcset can be set, and hasAvifSrcset() reflects its presence.
     */
    public function testAvifSrcsetGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasAvifSrcset());

        $image->setAvifSrcset('photo.avif 800w');
        $this->assertTrue($image->hasAvifSrcset());
        $this->assertSame('photo.avif 800w', $image->getAvifSrcset());
    }

    // --- WebP/AVIF single sources ---

    /**
     * Verify single WebP source can be set, and hasWebpSrc() reflects its presence.
     */
    public function testWebpSrcGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasWebpSrc());

        $image->setWebpSrc('/img/photo.webp');
        $this->assertTrue($image->hasWebpSrc());
        $this->assertSame('/img/photo.webp', $image->getWebpSrc());
    }

    /**
     * Verify single AVIF source can be set, and hasAvifSrc() reflects its presence.
     */
    public function testAvifSrcGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasAvifSrc());

        $image->setAvifSrc('/img/photo.avif');
        $this->assertTrue($image->hasAvifSrc());
        $this->assertSame('/img/photo.avif', $image->getAvifSrc());
    }

    // --- Caption ---

    /**
     * Verify caption can be set and retrieved, including HTML-stripped variant.
     */
    public function testCaptionGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasCaption());

        $image->setCaption('<p>A lovely photo</p>');
        $this->assertTrue($image->hasCaption());
        $this->assertSame('<p>A lovely photo</p>', $image->getCaption());
        $this->assertSame('A lovely photo', $image->getCaptionWithoutHTML());
    }

    // --- Sizes ---

    /**
     * Verify sizes attribute can be set, and hasSizes() reflects its presence.
     */
    public function testSizesGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasSizes());

        $image->setSizes('(max-width: 600px) 100vw, 50vw');
        $this->assertTrue($image->hasSizes());
        $this->assertSame('(max-width: 600px) 100vw, 50vw', $image->getSizes());
    }

    // --- Class ---

    /**
     * Verify CSS class can be set, and hasClass() reflects its presence.
     */
    public function testClassGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasClass());

        $image->setClass('hero-image');
        $this->assertTrue($image->hasClass());
        $this->assertSame('hero-image', $image->getClass());
    }

    // --- Fetch priority ---

    /**
     * Verify fetch priority can be set, and hasFetchPriority() reflects its presence.
     */
    public function testFetchPriorityGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasFetchPriority());

        $image->setFetchPriority('high');
        $this->assertTrue($image->hasFetchPriority());
        $this->assertSame('high', $image->getFetchPriority());
    }
}
