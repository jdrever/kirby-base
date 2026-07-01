<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

/**
 * Flat multi-select filter control with an "All" toggle.
 *
 * Renders a single "All" checkbox by default; unchecking it reveals the options
 * as a list of checkboxes (with an optional client-side filter). Submits the
 * selected options as an array via `name="{$name}[]"`; when "All" is active
 * nothing is submitted (no filter).
 *
 * Show/hide is handled with plain JS (toggling `d-none`) so it does not depend
 * on the Bootstrap collapse component being present in the site's build.
 *
 * Expected variables (injected by the caller via snippet()):
 * - string   $id             Unique base id for this control
 * - string   $name           Field name (submitted as {$name}[])
 * - string[] $options        Option values
 * - string[] $selectedValues Currently selected values (optional)
 * - string   $label          Overall control label (optional)
 * - string   $allLabel       Label for the "All" checkbox (optional, default "All")
 * - bool     $searchable     Show a client-side filter box (optional, default true)
 */

if (!isset($id)) :
    throw new Exception('checkbox-multiselect snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('checkbox-multiselect snippet: $name not provided');
endif;

if (!isset($options) || !is_array($options)) :
    throw new Exception('checkbox-multiselect snippet: $options not provided');
endif;

if (!isset($selectedValues) || !is_array($selectedValues)) :
    $selectedValues = [];
endif;

if (!isset($label)) :
    $label = '';
endif;

if (!isset($allLabel)) :
    $allLabel = 'All';
endif;

if (!isset($searchable)) :
    $searchable = true;
endif;

$options = array_values(array_filter($options, static fn($option): bool => trim((string) $option) !== ''));

if (empty($options)) :
    return;
endif;

$selectedCount = count($selectedValues);
$allChecked = $selectedCount === 0;
?>
<div class="container bg-light p-2 rounded my-2" id="<?= $id ?>" data-role="checkbox-multiselect">
    <?php if ($label !== '') : ?>
    <p class="fw-bold mb-2"><?= htmlspecialchars($label) ?> <span class="badge text-bg-success<?= $allChecked ? ' d-none' : '' ?>" data-role="selected-count"><?= $selectedCount ?></span></p>
    <?php endif ?>

    <div class="form-check form-check--flush">
        <input type="checkbox" class="form-check-input" id="<?= $id ?>_all" data-role="all" <?= $allChecked ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= $id ?>_all"><?= htmlspecialchars($allLabel) ?></label>
    </div>

    <div data-role="options" class="mt-2<?= $allChecked ? ' d-none' : '' ?>">
        <?php if ($searchable) : ?>
        <input
            type="text"
            class="form-control form-control-sm mb-2"
            data-role="filter"
            placeholder="Type to filter&hellip;"
            aria-label="Filter <?= htmlspecialchars($label !== '' ? $label : 'options') ?>"
            autocomplete="off"
        >
        <?php endif ?>

        <?php $optionIndex = 0;
        foreach ($options as $option) :
            $checkboxId = $id . '_' . $optionIndex; ?>
        <div class="form-check form-check--flush" data-role="option" data-label="<?= htmlspecialchars(mb_strtolower((string) $option)) ?>">
            <input
                type="checkbox"
                name="<?= $name ?>[]"
                id="<?= $checkboxId ?>"
                value="<?= htmlspecialchars((string) $option) ?>"
                class="form-check-input"
                <?= in_array($option, $selectedValues, true) ? 'checked' : '' ?>
            >
            <label class="form-check-label fs-6" for="<?= $checkboxId ?>"><?= htmlspecialchars((string) $option) ?></label>
        </div>
        <?php $optionIndex++;
        endforeach ?>
    </div>
</div>

<style>
/* Keep the standard .form-check markup but drop the negative-margin float that
   pulls the checkbox out to the left, so rows align with their container. */
.form-check--flush {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-height: auto;
    padding-left: 0;
    margin-bottom: 0;
}
.form-check--flush .form-check-input {
    float: none;
    flex-shrink: 0;
    margin-top: 0;
    margin-left: 0;
}
</style>

<script>
(function () {
    const root = document.getElementById(<?= json_encode($id) ?>);
    if (!root) {
        return;
    }

    const allCheckbox = root.querySelector('[data-role="all"]');
    const optionsWrap = root.querySelector('[data-role="options"]');
    const overallBadge = root.querySelector('[data-role="selected-count"]');

    function refreshCount() {
        const total = root.querySelectorAll('input[type="checkbox"][name]:checked').length;
        if (overallBadge) {
            overallBadge.textContent = total;
            overallBadge.classList.toggle('d-none', total === 0);
        }
        return total;
    }

    // "All" toggle: checking it clears every selection and hides the option list.
    if (allCheckbox) {
        allCheckbox.addEventListener('change', function () {
            if (allCheckbox.checked) {
                root.querySelectorAll('input[type="checkbox"][name]').forEach(function (cb) {
                    cb.checked = false;
                });
                refreshCount();
            }
            optionsWrap.classList.toggle('d-none', allCheckbox.checked);
        });
    }

    // Selecting any option means we are no longer in the "All" state.
    root.addEventListener('change', function (event) {
        if (event.target.matches('input[type="checkbox"][name]')) {
            const total = refreshCount();
            if (allCheckbox && total > 0) {
                allCheckbox.checked = false;
            }
        }
    });

    const filter = root.querySelector('[data-role="filter"]');
    if (filter) {
        filter.addEventListener('input', function () {
            const term = filter.value.trim().toLowerCase();
            root.querySelectorAll('[data-role="option"]').forEach(function (option) {
                const match = term === '' || option.getAttribute('data-label').indexOf(term) !== -1;
                option.classList.toggle('d-none', !match);
            });
        });
    }
})();
</script>
