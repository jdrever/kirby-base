<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\KeywordsProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the KeywordsProperties trait via an anonymous concrete class.
 */
final class KeywordsPropertiesTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use KeywordsProperties;
        };
    }

    public function testKeywordsDefaultToEmpty(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasKeywords());
        $this->assertSame('', $model->getKeywords());
    }

    public function testKeywordsGetterSetter(): void
    {
        $model = $this->createModel();
        $model->setKeywords('nature, wildlife, birds');

        $this->assertTrue($model->hasKeywords());
        $this->assertSame('nature, wildlife, birds', $model->getKeywords());
    }

    public function testSetKeywordsReturnsSelf(): void
    {
        $model = $this->createModel();

        $this->assertSame($model, $model->setKeywords('test'));
    }
}
