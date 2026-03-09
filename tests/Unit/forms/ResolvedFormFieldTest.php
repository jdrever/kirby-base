<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\forms;

use BSBI\WebBase\forms\ResolvedFormField;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ResolvedFormField value object and its snippet-arg helpers.
 */
final class ResolvedFormFieldTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $field = new ResolvedFormField(
            type:        'textbox',
            name:        'my_field',
            label:       'My Label',
            required:    true,
            inputType:   'email',
            help:        'Enter your email',
            options:     ['a', 'b'],
            leftLabel:   'Left',
            middleLabel: 'Middle',
            rightLabel:  'Right',
            scaleMin:    1,
            scaleMax:    7,
        );

        $this->assertSame('textbox', $field->type);
        $this->assertSame('my_field', $field->name);
        $this->assertSame('My Label', $field->label);
        $this->assertTrue($field->required);
        $this->assertSame('email', $field->inputType);
        $this->assertSame('Enter your email', $field->help);
        $this->assertSame(['a', 'b'], $field->options);
        $this->assertSame('Left', $field->leftLabel);
        $this->assertSame('Middle', $field->middleLabel);
        $this->assertSame('Right', $field->rightLabel);
        $this->assertSame(1, $field->scaleMin);
        $this->assertSame(7, $field->scaleMax);
    }

    public function testDefaultValues(): void
    {
        $field = new ResolvedFormField(type: 'textbox', name: 'x', label: 'X');

        $this->assertFalse($field->required);
        $this->assertSame('text', $field->inputType);
        $this->assertSame('', $field->help);
        $this->assertSame([], $field->options);
        $this->assertSame('Strongly disagree', $field->leftLabel);
        $this->assertSame('', $field->middleLabel);
        $this->assertSame('Strongly agree', $field->rightLabel);
        $this->assertSame(1, $field->scaleMin);
        $this->assertSame(5, $field->scaleMax);
    }

    public function testToTextboxArgsContainsRequiredKeys(): void
    {
        $field = new ResolvedFormField(
            type:      'textbox',
            name:      'email',
            label:     'Email address',
            required:  true,
            inputType: 'email',
        );

        $args = $field->toTextboxArgs();

        $this->assertSame('email', $args['id']);
        $this->assertSame('email', $args['name']);
        $this->assertSame('Email address', $args['label']);
        $this->assertSame('email', $args['type']);
        $this->assertTrue($args['required']);
    }

    public function testToTextareaArgsContainsRequiredKeys(): void
    {
        $field = new ResolvedFormField(
            type:     'textarea',
            name:     'comments',
            label:    'Your comments',
            required: false,
        );

        $args = $field->toTextareaArgs();

        $this->assertSame('comments', $args['id']);
        $this->assertSame('comments', $args['name']);
        $this->assertSame('Your comments', $args['label']);
        $this->assertFalse($args['required']);
    }

    public function testToSelectArgsFormatsOptionsAsValueDisplayPairs(): void
    {
        $field = new ResolvedFormField(
            type:    'select',
            name:    'country',
            label:   'Country',
            options: ['UK', 'Ireland'],
        );

        $args = $field->toSelectArgs();

        $this->assertSame('country', $args['id']);
        $this->assertSame('country', $args['name']);
        $this->assertSame('Country', $args['label']);
        $this->assertSame([
            ['value' => 'UK',      'display' => 'UK'],
            ['value' => 'Ireland', 'display' => 'Ireland'],
        ], $args['options']);
    }

    public function testToLikertArgsContainsAllLikertKeys(): void
    {
        $field = new ResolvedFormField(
            type:        'likert',
            name:        'satisfaction',
            label:       'How satisfied?',
            leftLabel:   'Not at all',
            middleLabel: 'Neutral',
            rightLabel:  'Very much',
            scaleMin:    1,
            scaleMax:    5,
        );

        $args = $field->toLikertArgs();

        $this->assertSame('satisfaction', $args['name']);
        $this->assertSame('How satisfied?', $args['label']);
        $this->assertSame('Not at all', $args['leftLabel']);
        $this->assertSame('Neutral', $args['middleLabel']);
        $this->assertSame('Very much', $args['rightLabel']);
        $this->assertSame(1, $args['scaleMin']);
        $this->assertSame(5, $args['scaleMax']);
    }
}
