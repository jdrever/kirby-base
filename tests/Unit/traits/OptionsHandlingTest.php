<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\OptionsHandling;
use PHPUnit\Framework\TestCase;

/**
 * Tests the OptionsHandling trait via a concrete test class that exposes protected methods.
 *
 * Covers createOption() value/display pair generation, getSimpleSelectOptions()
 * with automatic 'Any' option, and getSelectOptions() with/without 'Any'.
 */
final class OptionsHandlingTest extends TestCase
{
    /**
     * Create an anonymous class that exposes OptionsHandling protected methods.
     *
     * @return object
     */
    private function createModel(): object
    {
        return new class {
            use OptionsHandling;

            /** @param string[] $options */
            public function publicCreateOption(string $value, string $display): array
            {
                return $this->createOption($value, $display);
            }

            /** @param string[] $options */
            public function publicGetSimpleSelectOptions(array $options): array
            {
                return $this->getSimpleSelectOptions($options);
            }

            /** @param array<array{0: string, 1: string}> $options */
            public function publicGetSelectOptions(array $options, bool $includeAny = false): array
            {
                return $this->getSelectOptions($options, $includeAny);
            }
        };
    }

    /**
     * Verify createOption() returns an associative array with 'value' and 'display' keys.
     */
    public function testCreateOptionReturnsValueDisplayPair(): void
    {
        $model = $this->createModel();
        $option = $model->publicCreateOption('uk', 'United Kingdom');

        $this->assertSame(['value' => 'uk', 'display' => 'United Kingdom'], $option);
    }

    /**
     * Verify getSimpleSelectOptions() prepends an 'Any' option and maps each
     * string option to a value/display pair where both are the same string.
     */
    public function testGetSimpleSelectOptionsAddsAnyAndMapsOptions(): void
    {
        $model = $this->createModel();
        $result = $model->publicGetSimpleSelectOptions(['Red', 'Blue']);

        $this->assertCount(3, $result);
        $this->assertSame(['value' => '', 'display' => 'Any'], $result[0]);
        $this->assertSame(['value' => 'Red', 'display' => 'Red'], $result[1]);
        $this->assertSame(['value' => 'Blue', 'display' => 'Blue'], $result[2]);
    }

    /**
     * Verify getSelectOptions() maps [value, display] pairs without an 'Any' option.
     */
    public function testGetSelectOptionsWithoutAny(): void
    {
        $model = $this->createModel();
        $result = $model->publicGetSelectOptions([['gb', 'Great Britain'], ['us', 'United States']]);

        $this->assertCount(2, $result);
        $this->assertSame(['value' => 'gb', 'display' => 'Great Britain'], $result[0]);
        $this->assertSame(['value' => 'us', 'display' => 'United States'], $result[1]);
    }

    /**
     * Verify getSelectOptions() prepends an 'Any' option when includeAny is true.
     */
    public function testGetSelectOptionsWithAny(): void
    {
        $model = $this->createModel();
        $result = $model->publicGetSelectOptions([['gb', 'Great Britain']], true);

        $this->assertCount(2, $result);
        $this->assertSame(['value' => '', 'display' => 'Any'], $result[0]);
        $this->assertSame(['value' => 'gb', 'display' => 'Great Britain'], $result[1]);
    }
}
