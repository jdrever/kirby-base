<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\PostProperties;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PostProperties trait via a minimal anonymous concrete class.
 */
final class PostPropertiesTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use PostProperties;
        };
    }

    public function testSubtitleGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasSubtitle());

        $model->setSubtitle('A Subtitle');
        $this->assertTrue($model->hasSubtitle());
        $this->assertSame('A Subtitle', $model->getSubtitle());
    }

    public function testExcerptDefaultsToEmpty(): void
    {
        $model = $this->createModel();

        $this->assertSame('', $model->getExcerpt());
    }

    public function testExcerptGetterSetter(): void
    {
        $model = $this->createModel();
        $model->setExcerpt('A brief summary');

        $this->assertSame('A brief summary', $model->getExcerpt());
    }

    public function testPostedByGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasPostedBy());

        $model->setPostedBy('Jane Doe');
        $this->assertTrue($model->hasPostedBy());
        $this->assertSame('Jane Doe', $model->getPostedBy());
    }

    public function testPostedByUnknownTreatedAsNotSet(): void
    {
        $model = $this->createModel();
        $model->setPostedBy('Unknown');

        $this->assertFalse($model->hasPostedBy());
    }

    public function testPublicationDateGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasPublicationDate());

        $date = new DateTime('2024-03-15');
        $model->setPublicationDate($date);

        $this->assertTrue($model->hasPublicationDate());
        $this->assertSame($date, $model->getPublicationDate());
    }

    public function testFormattedPublicationDate(): void
    {
        $model = $this->createModel();
        $model->setPublicationDate(new DateTime('2024-03-15'));

        $this->assertSame('15 March 2024', $model->getFormattedPublicationDate());
    }
}
