<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\KeywordsProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the KeywordsProperties trait via an anonymous concrete class.
 *
 * Covers default empty state, getter/setter, and fluent return value.
 */
final class KeywordsPropertiesTest extends TestCase
{
    /**
     * Create an anonymous class that uses KeywordsProperties for testing.
     *
     * @return object
     */
    private function createModel(): object
    {
        return new class {
            use KeywordsProperties;
        };
    }

    /**
     * Verify keywords default to empty string and hasKeywords() returns false.
     */
    public function testKeywordsDefaultToEmpty(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasKeywords());
        $this->assertSame('', $model->getKeywords());
    }

    /**
     * Verify keywords can be set and retrieved.
     */
    public function testKeywordsGetterSetter(): void
    {
        $model = $this->createModel();
        $model->setKeywords('nature, wildlife, birds');

        $this->assertTrue($model->hasKeywords());
        $this->assertSame('nature, wildlife, birds', $model->getKeywords());
    }

    /**
     * Verify setKeywords() returns the same instance for fluent chaining.
     */
    public function testSetKeywordsReturnsSelf(): void
    {
        $model = $this->createModel();

        $this->assertSame($model, $model->setKeywords('test'));
    }
}
