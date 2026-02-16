<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Image;
use BSBI\WebBase\models\ImageList;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the ImageList model.
 *
 * Covers add/retrieve, empty-state behaviour, and getFirstXItems()
 * subset retrieval inherited from BaseList.
 */
final class ImageListTest extends TestCase
{
    /**
     * Create an Image with a given source URL for testing.
     *
     * @param string $src The image source URL
     * @return Image
     */
    private function createImage(string $src = '/img/photo.jpg'): Image
    {
        return new Image($src, '', '', 'Alt text', 800, 600);
    }

    /**
     * Verify images can be added and retrieved in insertion order.
     */
    public function testAddAndRetrieveImages(): void
    {
        $list = new ImageList();
        $img1 = $this->createImage('/img/one.jpg');
        $img2 = $this->createImage('/img/two.jpg');

        $list->addListItem($img1);
        $list->addListItem($img2);

        $this->assertSame(2, $list->count());
        $this->assertSame($img1, $list->getListItems()[0]);
    }

    /**
     * Verify a new ImageList is empty by default.
     */
    public function testEmptyByDefault(): void
    {
        $list = new ImageList();

        $this->assertSame(0, $list->count());
        $this->assertFalse($list->hasListItems());
    }

    /**
     * Verify getFirstXItems() returns only the requested number of items.
     */
    public function testGetFirstXItems(): void
    {
        $list = new ImageList();
        $list->addListItem($this->createImage('/img/one.jpg'));
        $list->addListItem($this->createImage('/img/two.jpg'));
        $list->addListItem($this->createImage('/img/three.jpg'));

        $first2 = $list->getFirstXItems(2);
        $this->assertCount(2, $first2);
    }
}
