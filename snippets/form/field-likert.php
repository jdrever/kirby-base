<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/** @var ResolvedFormField $field */

snippet('form/likert', $field->toLikertArgs()); ?>
<?php if ($field->help !== '') : ?><div class="form-text mt-1"><?= $field->help ?></div><?php endif; ?>
