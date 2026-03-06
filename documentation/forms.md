# Forms

The form system lets you define structured feedback/survey forms in code, store submissions as Kirby child pages, and export responses as CSV. Each form is built from five connected pieces.

## 1. Form definition class

Create a class extending `BaseFormDefinition` in your site's `classes/forms/` directory.

Override `defineForm()` and return any mix of `FormFieldSpec` and `FormSection` objects:

```php
class MyFormDefinition extends BaseFormDefinition
{
    public function getFormType(): string { return 'my_form'; }

    protected function defineForm(): array
    {
        return [
            FormFieldSpec::textbox('name', 'Your name')->required(),
            FormFieldSpec::radioGroup('role', 'Your role', ['Member', 'Volunteer'])
                ->overridable('label')
                ->overridable('options'),
            FormFieldSpec::textarea('feedback', 'Your feedback')->overridable('label'),
        ];
    }
}
```

**Sections with conditional display** — include `FormSection` objects anywhere in the returned array:

```php
protected function defineForm(): array
{
    return [
        FormFieldSpec::textbox('name', 'Your name')->required(),
        FormFieldSpec::radioGroup('contact_pref', 'Preferred contact', ['Email', 'Phone']),

        FormSection::make('email_section', 'Email details')
            ->fields(FormFieldSpec::textbox('email', 'Email address'))
            ->showWhen('contact_pref', 'Email'),

        FormSection::make('phone_section', 'Phone details')
            ->fields(FormFieldSpec::textbox('phone', 'Phone number'))
            ->showWhen('contact_pref', 'Phone'),
    ];
}
```

Sections are revealed client-side via vanilla JS when the controlling radio/select field value matches the expected value. Inputs inside hidden sections are disabled so they are not submitted.

`getFormType()` returns a short string (e.g. `'my_form'`) stored on every submission page for filtering and CSV export.

### FormFieldSpec types

| Factory method | HTML rendered as | Key options |
|----------------|------------------|-------------|
| `FormFieldSpec::textbox($name, $label)` | `<input type="text">` | `.required()`, `$inputType` param for email/date/etc |
| `FormFieldSpec::date($name, $label)` | `<input type="date">` | Shorthand for `textbox(..., 'date')` |
| `FormFieldSpec::textarea($name, $label)` | `<textarea>` | `.required()` |
| `FormFieldSpec::radioGroup($name, $label, $options)` | Radio buttons | `.overridable('options')` |
| `FormFieldSpec::checkboxGroup($name, $label, $options)` | Checkboxes | `.overridable('options')` |
| `FormFieldSpec::select($name, $label, $options)` | `<select>` | `.overridable('options')` |
| `FormFieldSpec::likert($name, $label)` | Scale buttons | `.overridable('leftLabel')`, `.overridable('rightLabel')` |

### Overridable properties

Calling `.overridable('label')` (or `'options'`, `'leftLabel'`, etc.) on a spec:
- Exposes that property as an editable panel field
- The panel field name is auto-derived: `{field_name}_{property_name}` in snake_case
  e.g. `knowledge_Start` + `leftLabel` → `knowledge_start_left_label`
- To generate ready-to-paste blueprint YAML for all overridable fields, call:
  ```php
  (new MyFormDefinition())->toBlueprintFields()
  ```

## 2. Page model class

Create a class extending `FormPage` in your site's `classes/models/` directory:

```php
class MyFormPage extends FormPage {}
```

No custom logic is needed unless you want to add form-specific properties.

## 3. Setter method in KirbyHelper

Add a protected setter method to your site's `KirbyHelper` class. The naming convention `set{ModelClass}(Page $page, ModelClass $model)` is important — `KirbyBaseHelper::getSpecificPage()` discovers and calls it automatically by matching the model class name:

```php
protected function setMyFormPage(Page $page, MyFormPage $myFormPage): MyFormPage
{
    $definition = new MyFormDefinition();
    $myFormPage->setFormFields($definition->getFields($page));
    $myFormPage->setFormFieldGroups($definition->getFieldGroups($page));

    if ($this->isPageFieldNotEmpty($page, 'customFormElements')) {
        $myFormPage->setCustomFormBlocks($this->getPageFieldAsBlocks($page, 'customFormElements'));
    }

    $this->setFormPage($page, $myFormPage, $definition->getFormType());
    return $myFormPage;
}
```

`setFormPage()` handles CSRF validation, Turnstile CAPTCHA, storing the submission as a child page, and sending the confirmation email.

## 4. Page blueprint

Create `blueprints/pages/my_form.yml`. The `form` tab includes:
- `formSection: sections/formFields` — standard fields (email recipient, success content, custom form blocks)
- `overridesSection` — panel-editable overrides for any `.overridable()` properties in your definition

```yaml
title: "My Form"
icon: draft
extends: layouts/bsbi-web-page

tabs:
  form:
    icon: file-text
    sections:
      formSection: sections/formFields
      overridesSection:
        type: fields
        label: Field overrides
        help: Leave any field blank to use the built-in default.
        fields:
          # Paste output of (new MyFormDefinition())->toBlueprintFields() here
          role_label:
            type: text
            label: "Label: Your role"
            help: Leave blank to use the default.
          role_options:
            type: textarea
            label: "Options: Your role (one per line)"
            help: Leave blank to use the default.

  responsesTab:
    icon: file-document
    label: Form Responses
    sections:
      exportSection:
        type: formsubmissionexport
        headline: Export Submissions
      responsesSection:
        label: Form Responses
        type: pages
        template: form_submission
```

## 5. Controller and template

**Controller** (`controllers/my_form.php`):

```php
return function ($page) {
    $helper = new KirbyHelper();
    $currentPage = $helper->getSpecificPage($page->id(), MyFormPage::class);
    return compact('currentPage');
};
```

**Template** (`templates/my_form.php`) — pass `$currentPage` to the `forms/definition-form` snippet:

```php
snippet('layout/content-page', slots: true);
  slot('lowerBody');
    snippet('forms/definition-form', ['currentPage' => $currentPage]);
  endslot();
endsnippet();
```

Create an intermediate snippet (e.g. `snippets/forms/my_form.php`) if you need content or logic specific to that form page surrounding the form itself.

## Summary

| Piece | Location | Purpose |
|-------|----------|---------|
| `MyFormDefinition` | `classes/forms/` | Declares fields, sections, and overridable properties |
| `MyFormPage` | `classes/models/` | Kirby page model — carries the resolved form state |
| `setMyFormPage()` | `KirbyHelper` | Wires definition → model; handles submission storage |
| `my_form.yml` | `blueprints/pages/` | Panel UI — field overrides, responses tab |
| `my_form.php` | `controllers/` + `templates/` | Fetches model; renders the form snippet |
