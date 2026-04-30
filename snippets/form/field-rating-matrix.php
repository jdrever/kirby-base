<?php

declare(strict_types=1);

use BSBI\WebBase\forms\ResolvedFormField;

/** @var ResolvedFormField $field */

?>
<p><strong><?= html($field->label) ?></strong></p>
<?php if ($field->help !== '') : ?><div class="form-text mb-2"><?= $field->help ?></div><?php endif; ?>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col"></th>
                <?php foreach ($field->columns as $column) : ?>
                    <th scope="col" class="text-center"><?= html($column) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($field->rows as $rowIndex => $row) :
                $rowKey = preg_replace('/[^a-z0-9]+/', '_', strtolower($row));
                $inputName = $field->name . '[' . $rowKey . ']';
            ?>
            <tr>
                <th scope="row" class="fw-normal"><?= html($row) ?></th>
                <?php foreach ($field->columns as $colIndex => $column) :
                    $inputId = html($field->name . '_' . $rowKey . '_' . $colIndex);
                ?>
                <td class="text-center">
                    <label class="visually-hidden" for="<?= $inputId ?>"><?= html($row) ?> — <?= html($column) ?></label>
                    <input
                        type="radio"
                        class="form-check-input"
                        name="<?= html($inputName) ?>"
                        id="<?= $inputId ?>"
                        value="<?= html($column) ?>"
                        <?php if ($field->required && $colIndex === 0) : ?>required<?php endif; ?>
                    >
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
