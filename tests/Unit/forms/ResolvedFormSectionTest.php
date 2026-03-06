<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\forms;

use BSBI\WebBase\forms\ResolvedFormField;
use BSBI\WebBase\forms\ResolvedFormSection;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ResolvedFormSection.
 */
final class ResolvedFormSectionTest extends TestCase
{
    private function makeField(string $name): ResolvedFormField
    {
        return new ResolvedFormField(
            type: 'textbox',
            name: $name,
            label: 'Label',
        );
    }

    public function testConstructorStoresAllProperties(): void
    {
        $field = $this->makeField('email');

        $section = new ResolvedFormSection(
            id:             'contact',
            title:          'Contact',
            fields:         [$field],
            conditionField: 'preference',
            conditionValue: 'email',
        );

        $this->assertSame('contact', $section->id);
        $this->assertSame('Contact', $section->title);
        $this->assertCount(1, $section->fields);
        $this->assertSame('preference', $section->conditionField);
        $this->assertSame('email', $section->conditionValue);
    }

    public function testIsConditionalReturnsTrueWhenConditionFieldSet(): void
    {
        $section = new ResolvedFormSection(
            id:             's',
            title:          '',
            fields:         [],
            conditionField: 'my_field',
            conditionValue: 'yes',
        );

        $this->assertTrue($section->isConditional());
    }

    public function testIsConditionalReturnsFalseWhenConditionFieldNull(): void
    {
        $section = new ResolvedFormSection(
            id:             's',
            title:          '',
            fields:         [],
            conditionField: null,
            conditionValue: null,
        );

        $this->assertFalse($section->isConditional());
    }

    public function testEmptyTitleAllowed(): void
    {
        $section = new ResolvedFormSection(
            id:             's',
            title:          '',
            fields:         [],
            conditionField: null,
            conditionValue: null,
        );

        $this->assertSame('', $section->title);
    }

    public function testFieldsAreAccessible(): void
    {
        $f1 = $this->makeField('a');
        $f2 = $this->makeField('b');

        $section = new ResolvedFormSection(
            id:             's',
            title:          '',
            fields:         [$f1, $f2],
            conditionField: null,
            conditionValue: null,
        );

        $this->assertCount(2, $section->fields);
        $this->assertSame('a', $section->fields[0]->name);
        $this->assertSame('b', $section->fields[1]->name);
    }
}
