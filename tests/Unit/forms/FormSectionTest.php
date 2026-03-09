<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\forms;

use BSBI\WebBase\forms\FormFieldSpec;
use BSBI\WebBase\forms\FormSection;
use BSBI\WebBase\forms\ResolvedFormField;
use BSBI\WebBase\forms\ResolvedFormSection;
use Kirby\Cms\Page;
use Kirby\Content\Content;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FormSection.
 */
final class FormSectionTest extends TestCase
{
    // ── make / accessors ─────────────────────────────────────────────────────

    public function testMakeReturnsFormSection(): void
    {
        $section = FormSection::make('my_section', 'My Section');
        $this->assertInstanceOf(FormSection::class, $section);
    }

    public function testMakeStoresIdAndTitle(): void
    {
        $section = FormSection::make('contact', 'Contact Details');
        $this->assertSame('contact', $section->getId());
        $this->assertSame('Contact Details', $section->getTitle());
    }

    public function testMakeWithEmptyTitleDefaultsToEmptyString(): void
    {
        $section = FormSection::make('untitled');
        $this->assertSame('', $section->getTitle());
    }

    // ── fields ───────────────────────────────────────────────────────────────

    public function testFieldsAddsSpecs(): void
    {
        $spec1 = FormFieldSpec::textbox('email', 'Email');
        $spec2 = FormFieldSpec::textbox('phone', 'Phone');

        $section = FormSection::make('contact')->fields($spec1, $spec2);

        $this->assertCount(2, $section->getFields());
        $this->assertSame('email', $section->getFields()[0]->getName());
        $this->assertSame('phone', $section->getFields()[1]->getName());
    }

    public function testFieldsCanBeCalledMultipleTimes(): void
    {
        $section = FormSection::make('multi')
            ->fields(FormFieldSpec::textbox('a', 'A'))
            ->fields(FormFieldSpec::textbox('b', 'B'));

        $this->assertCount(2, $section->getFields());
    }

    public function testGetFieldsEmptyByDefault(): void
    {
        $section = FormSection::make('empty');
        $this->assertSame([], $section->getFields());
    }

    // ── showWhen ─────────────────────────────────────────────────────────────

    public function testShowWhenStoresCondition(): void
    {
        $section = FormSection::make('s')->showWhen('role', 'admin');

        $this->assertSame('role', $section->getConditionField());
        $this->assertSame('admin', $section->getConditionValue());
    }

    public function testNoConditionByDefault(): void
    {
        $section = FormSection::make('s');
        $this->assertNull($section->getConditionField());
        $this->assertNull($section->getConditionValue());
    }

    // ── resolve ──────────────────────────────────────────────────────────────

    public function testResolveReturnsResolvedFormSection(): void
    {
        $section = FormSection::make('details', 'Details')
            ->fields(FormFieldSpec::textbox('name', 'Name'));

        $page = $this->makePage();

        $resolved = $section->resolve($page, fn(FormFieldSpec $s, Page $p) => $s->resolve([]));

        $this->assertInstanceOf(ResolvedFormSection::class, $resolved);
        $this->assertSame('details', $resolved->id);
        $this->assertSame('Details', $resolved->title);
    }

    public function testResolveReturnsResolvedFields(): void
    {
        $section = FormSection::make('s')
            ->fields(
                FormFieldSpec::textbox('email', 'Email'),
                FormFieldSpec::textbox('phone', 'Phone'),
            );

        $page = $this->makePage();
        $resolved = $section->resolve($page, fn(FormFieldSpec $s, Page $p) => $s->resolve([]));

        $this->assertCount(2, $resolved->fields);
        $this->assertInstanceOf(ResolvedFormField::class, $resolved->fields[0]);
        $this->assertSame('email', $resolved->fields[0]->name);
    }

    public function testResolvePassesCondition(): void
    {
        $section = FormSection::make('s')
            ->showWhen('type', 'advanced');

        $page = $this->makePage();
        $resolved = $section->resolve($page, fn(FormFieldSpec $s, Page $p) => $s->resolve([]));

        $this->assertTrue($resolved->isConditional());
        $this->assertSame('type', $resolved->conditionField);
        $this->assertSame('advanced', $resolved->conditionValue);
    }

    public function testResolveUnconditionalSectionIsNotConditional(): void
    {
        $section = FormSection::make('s');
        $page = $this->makePage();
        $resolved = $section->resolve($page, fn(FormFieldSpec $s, Page $p) => $s->resolve([]));

        $this->assertFalse($resolved->isConditional());
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function makePage(): Page
    {
        $page = $this->createMock(Page::class);
        $content = $this->createMock(Content::class);
        $content->method('get')->willReturnCallback(function (string $key) {
            $field = $this->createMock(\Kirby\Cms\Field::class);
            $field->method('value')->willReturn('');
            $field->method('__toString')->willReturn('');
            return $field;
        });
        $page->method('content')->willReturn($content);
        return $page;
    }
}
