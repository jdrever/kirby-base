<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\Image;
use BSBI\WebBase\models\WebPageLink;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ImageHandling trait via WebPageLink (a concrete user of the trait).
 *
 * Covers hasImage default state, setImage/getImage roundtrip,
 * and fluent setter return value.
 */
final class ImageHandlingTest extends TestCase
{
    /**
     * Create a WebPageLink for testing ImageHandling methods.
     *
     * @return WebPageLink
     */
    private function createLink(): WebPageLink
    {
        return new WebPageLink('Test', '/test', 'test', 'default');
    }

    /**
     * Verify hasImage() returns false when no image has been set.
     */
    public function testHasImageReturnsFalseByDefault(): void
    {
        $link = $this->createLink();

        $this->assertFalse($link->hasImage());
    }

    /**
     * Verify an image can be set and retrieved, and hasImage() reflects its presence.
     */
    public function testSetAndGetImage(): void
    {
        $link = $this->createLink();
        $image = new Image('/img/hero.jpg', '', '', 'Hero', 1200, 800);

        $link->setImage($image);

        $this->assertTrue($link->hasImage());
        $this->assertSame($image, $link->getImage());
    }

    /**
     * Verify setImage() returns the same instance for fluent chaining.
     */
    public function testSetImageReturnsSelf(): void
    {
        $link = $this->createLink();
        $image = new Image('/img/test.jpg');

        $this->assertSame($link, $link->setImage($image));
    }
}
