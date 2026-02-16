<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Image;
use PHPUnit\Framework\TestCase;

final class ImageTest extends TestCase
{
    private function createImage(
        string $src = '/img/photo.jpg',
        string $alt = 'A photo',
        int $width = 800,
        int $height = 600,
    ): Image {
        return new Image($src, '', '', $alt, $width, $height);
    }

    // --- Constructor defaults ---

    public function testConstructorSetsBasicProperties(): void
    {
        $image = $this->createImage();

        $this->assertSame('/img/photo.jpg', $image->getSrc());
        $this->assertSame('A photo', $image->getAlt());
        $this->assertSame(800, $image->getWidth());
        $this->assertSame(600, $image->getHeight());
    }

    public function testDefaultLoadingAndDecoding(): void
    {
        $image = $this->createImage();

        $this->assertSame('lazy', $image->getLoading());
        $this->assertSame('async', $image->getDecoding());
    }

    // --- isAvailable ---

    public function testIsAvailableWhenSrcIsSet(): void
    {
        $image = $this->createImage();
        $this->assertTrue($image->isAvailable());
    }

    public function testIsNotAvailableWhenSrcIsEmpty(): void
    {
        $image = new Image();
        $this->assertFalse($image->isAvailable());
    }

    // --- Srcset variants ---

    public function testSrcsetGetterSetter(): void
    {
        $image = $this->createImage();
        $image->setSrcset('photo-320.jpg 320w, photo-640.jpg 640w');

        $this->assertSame('photo-320.jpg 320w, photo-640.jpg 640w', $image->getSrcset());
    }

    public function testWebpSrcsetGetterSetter(): void
    {
        $image = $this->createImage();
        $image->setWebpSrcset('photo.webp 800w');

        $this->assertSame('photo.webp 800w', $image->getWebpSrcset());
    }

    public function testAvifSrcsetGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasAvifSrcset());

        $image->setAvifSrcset('photo.avif 800w');
        $this->assertTrue($image->hasAvifSrcset());
        $this->assertSame('photo.avif 800w', $image->getAvifSrcset());
    }

    // --- WebP/AVIF single sources ---

    public function testWebpSrcGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasWebpSrc());

        $image->setWebpSrc('/img/photo.webp');
        $this->assertTrue($image->hasWebpSrc());
        $this->assertSame('/img/photo.webp', $image->getWebpSrc());
    }

    public function testAvifSrcGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasAvifSrc());

        $image->setAvifSrc('/img/photo.avif');
        $this->assertTrue($image->hasAvifSrc());
        $this->assertSame('/img/photo.avif', $image->getAvifSrc());
    }

    // --- Caption ---

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

    public function testSizesGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasSizes());

        $image->setSizes('(max-width: 600px) 100vw, 50vw');
        $this->assertTrue($image->hasSizes());
        $this->assertSame('(max-width: 600px) 100vw, 50vw', $image->getSizes());
    }

    // --- Class ---

    public function testClassGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasClass());

        $image->setClass('hero-image');
        $this->assertTrue($image->hasClass());
        $this->assertSame('hero-image', $image->getClass());
    }

    // --- Fetch priority ---

    public function testFetchPriorityGetterSetter(): void
    {
        $image = $this->createImage();

        $this->assertFalse($image->hasFetchPriority());

        $image->setFetchPriority('high');
        $this->assertTrue($image->hasFetchPriority());
        $this->assertSame('high', $image->getFetchPriority());
    }
}
