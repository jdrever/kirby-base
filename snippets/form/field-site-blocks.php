<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/**
 * Renders the HTML output of a Kirby blocks field stored on the site object.
 * No form input is generated; this field is display-only.
 *
 * Expected variable:
 *   $field  ResolvedFormField  The resolved field; $field->content holds the rendered blocks HTML
 */

/** @var ResolvedFormField $field */

echo $field->content;
