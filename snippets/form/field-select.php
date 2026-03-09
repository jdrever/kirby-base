<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/** @var ResolvedFormField $field */

snippet('form/select', $field->toSelectArgs()); ?>
<?php if ($field->help !== '') : ?><div class="form-text mt-1"><?= $field->help ?></div><?php endif; ?>
