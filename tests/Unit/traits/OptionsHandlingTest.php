<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\OptionsHandling;
use PHPUnit\Framework\TestCase;

/**
 * Tests the OptionsHandling trait via a concrete test class that exposes protected methods.
 */
final class OptionsHandlingTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use OptionsHandling;

            public function publicCreateOption(string $value, string $display): array
            {
                return $this->createOption($value, $display);
            }

            public function publicGetSimpleSelectOptions(array $options): array
            {
                return $this->getSimpleSelectOptions($options);
            }

            public function publicGetSelectOptions(array $options, bool $includeAny = false): array
            {
                return $this->getSelectOptions($options, $includeAny);
            }
        };
    }

    public function testCreateOptionReturnsValueDisplayPair(): void
    {
        $model = $this->createModel();
        $option = $model->publicCreateOption('uk', 'United Kingdom');

        $this->assertSame(['value' => 'uk', 'display' => 'United Kingdom'], $option);
    }

    public function testGetSimpleSelectOptionsAddsAnyAndMapsOptions(): void
    {
        $model = $this->createModel();
        $result = $model->publicGetSimpleSelectOptions(['Red', 'Blue']);

        $this->assertCount(3, $result);
        $this->assertSame(['value' => '', 'display' => 'Any'], $result[0]);
        $this->assertSame(['value' => 'Red', 'display' => 'Red'], $result[1]);
        $this->assertSame(['value' => 'Blue', 'display' => 'Blue'], $result[2]);
    }

    public function testGetSelectOptionsWithoutAny(): void
    {
        $model = $this->createModel();
        $result = $model->publicGetSelectOptions([['gb', 'Great Britain'], ['us', 'United States']]);

        $this->assertCount(2, $result);
        $this->assertSame(['value' => 'gb', 'display' => 'Great Britain'], $result[0]);
        $this->assertSame(['value' => 'us', 'display' => 'United States'], $result[1]);
    }

    public function testGetSelectOptionsWithAny(): void
    {
        $model = $this->createModel();
        $result = $model->publicGetSelectOptions([['gb', 'Great Britain']], true);

        $this->assertCount(2, $result);
        $this->assertSame(['value' => '', 'display' => 'Any'], $result[0]);
        $this->assertSame(['value' => 'gb', 'display' => 'Great Britain'], $result[1]);
    }
}
