<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\forms;

use BSBI\WebBase\forms\FormFieldSpec;
use BSBI\WebBase\forms\ResolvedFormField;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FormFieldSpec fluent builder and resolution logic.
 */
final class FormFieldSpecTest extends TestCase
{
    // ── Factory methods ─────────────────────────────────────────────────────

    public function testTextboxFactoryCreatesCorrectType(): void
    {
        $spec = FormFieldSpec::textbox('my_field', 'My Label');
        $this->assertSame(FormFieldSpec::TYPE_TEXTBOX, $spec->getType());
        $this->assertSame('my_field', $spec->getName());
    }

    public function testDateFactoryCreatesTextboxWithDateInputType(): void
    {
        $spec = FormFieldSpec::date('event_date', 'Event Date');
        $this->assertSame(FormFieldSpec::TYPE_TEXTBOX, $spec->getType());
        $resolved = $spec->resolve([]);
        $this->assertSame('date', $resolved->inputType);
    }

    public function testTextareaFactoryCreatesCorrectType(): void
    {
        $spec = FormFieldSpec::textarea('comments', 'Comments');
        $this->assertSame(FormFieldSpec::TYPE_TEXTAREA, $spec->getType());
    }

    public function testCheckboxGroupFactoryCreatesCorrectType(): void
    {
        $spec = FormFieldSpec::checkboxGroup('choices', 'Pick options', ['A', 'B']);
        $this->assertSame(FormFieldSpec::TYPE_CHECKBOX_GROUP, $spec->getType());
    }

    public function testRadioGroupFactoryCreatesCorrectType(): void
    {
        $spec = FormFieldSpec::radioGroup('yn', 'Yes or no?', ['Yes', 'No']);
        $this->assertSame(FormFieldSpec::TYPE_RADIO_GROUP, $spec->getType());
    }

    public function testLikertFactoryCreatesCorrectType(): void
    {
        $spec = FormFieldSpec::likert('satisfaction', 'How satisfied are you?');
        $this->assertSame(FormFieldSpec::TYPE_LIKERT, $spec->getType());
    }

    public function testSelectFactoryCreatesCorrectType(): void
    {
        $spec = FormFieldSpec::select('country', 'Country', ['UK', 'IE']);
        $this->assertSame(FormFieldSpec::TYPE_SELECT, $spec->getType());
    }

    // ── required() ─────────────────────────────────────────────────────────

    public function testRequiredDefaultsFalse(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Name');
        $this->assertFalse($spec->resolve([])->required);
    }

    public function testRequiredFluent(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Name')->required();
        $this->assertTrue($spec->resolve([])->required);
    }

    // ── getBlueprintFieldName ───────────────────────────────────────────────

    public function testBlueprintFieldNameCombinesSnakeCaseParts(): void
    {
        $spec = FormFieldSpec::likert('knowledge_Start', 'Knowledge');
        $this->assertSame('knowledge_start_left_label', $spec->getBlueprintFieldName('leftLabel'));
        $this->assertSame('knowledge_start_right_label', $spec->getBlueprintFieldName('rightLabel'));
        $this->assertSame('knowledge_start_label', $spec->getBlueprintFieldName('label'));
    }

    public function testBlueprintFieldNameForSimpleField(): void
    {
        $spec = FormFieldSpec::checkboxGroup('Best_Describes_You', 'Who are you?');
        $this->assertSame('best_describes_you_label', $spec->getBlueprintFieldName('label'));
        $this->assertSame('best_describes_you_options', $spec->getBlueprintFieldName('options'));
    }

    // ── resolve() – label ───────────────────────────────────────────────────

    public function testResolveUsesDefaultLabelWhenNoPanelValue(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Your Name');
        $resolved = $spec->resolve([]);
        $this->assertSame('Your Name', $resolved->label);
    }

    public function testResolvePanelValueOverridesLabel(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Your Name')->overridable('label');
        $resolved = $spec->resolve(['label' => 'Full Name']);
        $this->assertSame('Full Name', $resolved->label);
    }

    public function testResolveExplicitOverridableDefaultUsedWhenPanelEmpty(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Your Name')->overridable('label', 'Preferred Name');
        $resolved = $spec->resolve([]);
        $this->assertSame('Preferred Name', $resolved->label);
    }

    public function testResolvePanelValueTakesPriorityOverExplicitDefault(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Your Name')->overridable('label', 'Preferred Name');
        $resolved = $spec->resolve(['label' => 'Full Name']);
        $this->assertSame('Full Name', $resolved->label);
    }

    // ── resolve() – options ─────────────────────────────────────────────────

    public function testResolveUsesDefaultOptionsWhenNoPanelValue(): void
    {
        $spec = FormFieldSpec::checkboxGroup('role', 'Role', ['A', 'B', 'C']);
        $resolved = $spec->resolve([]);
        $this->assertSame(['A', 'B', 'C'], $resolved->options);
    }

    public function testResolveParsesNewlineSeparatedPanelOptions(): void
    {
        $spec = FormFieldSpec::checkboxGroup('role', 'Role', ['A', 'B'])->overridable('options');
        $resolved = $spec->resolve(['options' => "X\nY\nZ"]);
        $this->assertSame(['X', 'Y', 'Z'], $resolved->options);
    }

    public function testResolveStripsBlankLinesFromPanelOptions(): void
    {
        $spec = FormFieldSpec::checkboxGroup('role', 'Role')->overridable('options');
        $resolved = $spec->resolve(['options' => "X\n\nY\n"]);
        $this->assertSame(['X', 'Y'], $resolved->options);
    }

    // ── resolve() – likert labels ───────────────────────────────────────────

    public function testResolveLikertUsesDefaultLabels(): void
    {
        $spec = FormFieldSpec::likert('q', 'Question', 'Left default', '', 'Right default');
        $resolved = $spec->resolve([]);
        $this->assertSame('Left default', $resolved->leftLabel);
        $this->assertSame('Right default', $resolved->rightLabel);
    }

    public function testResolveLikertPanelOverridesLeftRightLabels(): void
    {
        $spec = FormFieldSpec::likert('q', 'Question', 'Left default', '', 'Right default')
            ->overridable('leftLabel', 'Left default')
            ->overridable('rightLabel', 'Right default');
        $resolved = $spec->resolve(['leftLabel' => 'Completely wrong', 'rightLabel' => 'Completely right']);
        $this->assertSame('Completely wrong', $resolved->leftLabel);
        $this->assertSame('Completely right', $resolved->rightLabel);
    }

    // ── toBlueprintFields() ────────────────────────────────────────────────

    public function testToBlueprintFieldsReturnsEmptyArrayWhenNoOverridables(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Name');
        $this->assertSame([], $spec->toBlueprintFields());
    }

    public function testToBlueprintFieldsReturnsFieldForEachOverridable(): void
    {
        $spec = FormFieldSpec::likert('knowledge_Start', 'Knowledge')
            ->overridable('label')
            ->overridable('leftLabel');
        $fields = $spec->toBlueprintFields();
        $this->assertArrayHasKey('knowledge_start_label', $fields);
        $this->assertArrayHasKey('knowledge_start_left_label', $fields);
        $this->assertSame('text', $fields['knowledge_start_label']['type']);
    }

    public function testToBlueprintFieldsOptionsUsesTextareaType(): void
    {
        $spec = FormFieldSpec::checkboxGroup('role', 'Role')->overridable('options');
        $fields = $spec->toBlueprintFields();
        $this->assertSame('textarea', $fields['role_options']['type']);
    }

    // ── getOverridableProperties() ─────────────────────────────────────────

    public function testGetOverridablePropertiesReturnsRegisteredProperties(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Name')
            ->overridable('label')
            ->overridable('rightLabel', 'Right');
        $props = $spec->getOverridableProperties();
        $this->assertArrayHasKey('label', $props);
        $this->assertArrayHasKey('rightLabel', $props);
    }

    // ── resolve() returns correct ResolvedFormField type ───────────────────

    public function testResolveReturnsResolvedFormField(): void
    {
        $spec = FormFieldSpec::textbox('name', 'Name');
        $this->assertInstanceOf(ResolvedFormField::class, $spec->resolve([]));
    }
}
