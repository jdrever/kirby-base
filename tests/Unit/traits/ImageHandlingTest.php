<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\Image;
use BSBI\WebBase\models\WebPageLink;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ImageHandling trait via WebPageLink (a concrete user of the trait).
 */
final class ImageHandlingTest extends TestCase
{
    private function createLink(): WebPageLink
    {
        return new WebPageLink('Test', '/test', 'test', 'default');
    }

    public function testHasImageReturnsFalseByDefault(): void
    {
        $link = $this->createLink();

        $this->assertFalse($link->hasImage());
    }

    public function testSetAndGetImage(): void
    {
        $link = $this->createLink();
        $image = new Image('/img/hero.jpg', '', '', 'Hero', 1200, 800);

        $link->setImage($image);

        $this->assertTrue($link->hasImage());
        $this->assertSame($image, $link->getImage());
    }

    public function testSetImageReturnsSelf(): void
    {
        $link = $this->createLink();
        $image = new Image('/img/test.jpg');

        $this->assertSame($link, $link->setImage($image));
    }
}
