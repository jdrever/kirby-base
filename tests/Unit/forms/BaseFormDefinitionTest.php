<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\forms;

use BSBI\WebBase\forms\BaseFormDefinition;
use BSBI\WebBase\forms\FormFieldSpec;
use BSBI\WebBase\forms\ResolvedFormField;
use Kirby\Cms\Page;
use Kirby\Cms\ContentTranslation;
use Kirby\Content\Content;
use PHPUnit\Framework\TestCase;

/**
 * Tests for BaseFormDefinition using a minimal concrete implementation.
 */
final class BaseFormDefinitionTest extends TestCase
{
    // ── Fixture ─────────────────────────────────────────────────────────────

    /**
     * Creates a minimal concrete BaseFormDefinition for testing.
     *
     * @param FormFieldSpec[] $fields
     * @param string          $formType
     */
    private function makeDefinition(array $fields, string $formType = 'test_form'): BaseFormDefinition
    {
        return new class ($fields, $formType) extends BaseFormDefinition {
            public function __construct(
                private readonly array $testFields,
                private readonly string $testFormType,
            ) {
            }

            public function getFormType(): string
            {
                return $this->testFormType;
            }

            protected function defineFields(): array
            {
                return $this->testFields;
            }
        };
    }

    /**
     * Creates a mock Kirby Page where content()->get($field)->value() returns
     * the value from $contentMap, or '' if not present.
     *
     * @param array<string, string> $contentMap
     */
    private function makePage(array $contentMap = []): Page
    {
        $page = $this->createMock(Page::class);
        $content = $this->createMock(Content::class);

        $content->method('get')->willReturnCallback(
            function (string $key) use ($contentMap) {
                $field = $this->createMock(\Kirby\Cms\Field::class);
                $field->method('value')->willReturn($contentMap[$key] ?? '');
                $field->method('__toString')->willReturn($contentMap[$key] ?? '');
                return $field;
            }
        );

        $page->method('content')->willReturn($content);
        return $page;
    }

    // ── getFormType ─────────────────────────────────────────────────────────

    public function testGetFormTypeReturnsDefinedType(): void
    {
        $definition = $this->makeDefinition([], 'my_form_type');
        $this->assertSame('my_form_type', $definition->getFormType());
    }

    // ── getFieldNames ───────────────────────────────────────────────────────

    public function testGetFieldNamesReturnsNamesInOrder(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('first_name', 'First Name'),
            FormFieldSpec::textbox('last_name', 'Last Name'),
            FormFieldSpec::textarea('comments', 'Comments'),
        ]);

        $this->assertSame(['first_name', 'last_name', 'comments'], $definition->getFieldNames());
    }

    public function testGetFieldNamesEmptyWhenNoFields(): void
    {
        $definition = $this->makeDefinition([]);
        $this->assertSame([], $definition->getFieldNames());
    }

    // ── getFields ───────────────────────────────────────────────────────────

    public function testGetFieldsReturnsResolvedFormFieldInstances(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('name', 'Name'),
        ]);

        $page   = $this->makePage();
        $fields = $definition->getFields($page);

        $this->assertCount(1, $fields);
        $this->assertInstanceOf(ResolvedFormField::class, $fields[0]);
    }

    public function testGetFieldsUsesDefaultLabelWhenPageValueEmpty(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('location', 'Workshop Location')->overridable('label'),
        ]);

        $page   = $this->makePage([]);
        $fields = $definition->getFields($page);

        $this->assertSame('Workshop Location', $fields[0]->label);
    }

    public function testGetFieldsAppliesPanelOverrideForLabel(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('location', 'Workshop Location')->overridable('label'),
        ]);

        // The blueprint field name for 'location' + 'label' is 'location_label'
        $page   = $this->makePage(['location_label' => 'Event Location']);
        $fields = $definition->getFields($page);

        $this->assertSame('Event Location', $fields[0]->label);
    }

    public function testGetFieldsAppliesPanelOverrideForOptions(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::checkboxGroup('role', 'Role', ['A', 'B'])->overridable('options'),
        ]);

        $page   = $this->makePage(['role_options' => "X\nY\nZ"]);
        $fields = $definition->getFields($page);

        $this->assertSame(['X', 'Y', 'Z'], $fields[0]->options);
    }

    public function testGetFieldsPreservesOrderOfFields(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('a', 'Field A'),
            FormFieldSpec::textbox('b', 'Field B'),
            FormFieldSpec::textbox('c', 'Field C'),
        ]);

        $page   = $this->makePage();
        $fields = $definition->getFields($page);

        $this->assertSame('a', $fields[0]->name);
        $this->assertSame('b', $fields[1]->name);
        $this->assertSame('c', $fields[2]->name);
    }

    // ── toBlueprintFields ───────────────────────────────────────────────────

    public function testToBlueprintFieldsMergesAllFieldOverridables(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('location', 'Location')->overridable('label'),
            FormFieldSpec::likert('knowledge', 'Knowledge')
                ->overridable('label')
                ->overridable('leftLabel'),
        ]);

        $blueprintFields = $definition->toBlueprintFields();

        $this->assertArrayHasKey('location_label', $blueprintFields);
        $this->assertArrayHasKey('knowledge_label', $blueprintFields);
        $this->assertArrayHasKey('knowledge_left_label', $blueprintFields);
    }

    public function testToBlueprintFieldsEmptyWhenNoOverridables(): void
    {
        $definition = $this->makeDefinition([
            FormFieldSpec::textbox('name', 'Name'),
        ]);

        $this->assertSame([], $definition->toBlueprintFields());
    }
}
