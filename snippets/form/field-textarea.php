<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/** @var ResolvedFormField $field */

snippet('form/textarea', $field->toTextareaArgs());
<?php if ($field->help !== '') : ?><div class="form-text mt-1"><?= $field->help ?></div><?php endif; ?>
