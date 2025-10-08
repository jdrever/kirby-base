<?php

declare(strict_types=1);

$name = $name ?? 'q1_satisfaction';
$label = $label ?? 'How satisfied are you with the service?';
$scaleMin     = $scaleMin ?? 1;
$scaleMax     = $scaleMax ?? 5;
$leftLabel    = $leftLabel ?? 'Strongly disagree';
$middleLabel = $middleLabel ?? 'Netural';
$rightLabel   = $rightLabel ?? 'Strongly agree';
?>

<div class="card p-2 mb-2">
    <?php if (isset($label)) : ?>
    <p>
        <?= html($label) ?>
    </p>
    <?php endif ?>
    <div class="row g-2 g-sm-4 align-items-stretch">
        <div class="col-6 col-sm-8 d-flex justify-content-center">
            <div class="d-flex justify-content-between w-100" style="max-width: 500px;">
                <?php for ($i = $scaleMin; $i <= $scaleMax; $i++): ?>
                    <?php
                    // Unique ID for label/input pairing
                    $inputId = $name . '-' . $i;
                    ?>
                    <label for="<?= $inputId ?>" class="likert-item">
                        <span class="likert-label"><?= $i ?></span>
                        <input
                                class="form-check-input"
                                type="radio"
                                name="<?= $name ?>"
                                id="<?= $inputId ?>"
                                value="<?= $i ?>"
                        >
                    </label>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-between mx-auto px-0 px-sm-5" style="max-width: 600px;">
            <small class="text-danger fw-medium"><?=$leftLabel?></small>
            <small class="text-secondary fw-medium"><?=$middleLabel?></small>
            <small class="text-success fw-medium"><?=$rightLabel?></small>
        </div>
    </div>
</div>
