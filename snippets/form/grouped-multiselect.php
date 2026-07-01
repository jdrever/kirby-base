<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

/**
 * Grouped multi-select filter control with an "All" toggle.
 *
 * Renders a single "All" checkbox by default. Unchecking it reveals a list of
 * groups (e.g. countries); expanding a group reveals its options (e.g. vice
 * counties) as checkboxes. Suitable for long option lists where a flat
 * multi-select would be unwieldy. Submits selected options as an array via
 * `name="{$name}[]"`; when "All" is active nothing is submitted (no filter).
 *
 * Show/hide is handled with plain JS (toggling `d-none`) rather than the
 * Bootstrap accordion/collapse components, so it does not depend on those
 * component styles being present in the site's Bootstrap build.
 *
 * Expected variables (injected by the caller via snippet()):
 * - string                  $id             Unique base id for this control
 * - string                  $name           Field name (submitted as {$name}[])
 * - array<string, string[]> $groups         Group label => list of option values
 * - string[]                $selectedValues Currently selected values (optional)
 * - string                  $label          Overall control label (optional)
 * - string                  $allLabel       Label for the "All" checkbox (optional, default "All")
 * - string                  $groupName      Field name for group-level selection, e.g.
 *                                           selecting a whole country (optional; submitted
 *                                           as {$groupName}[]). When set, each group header
 *                                           gains its own checkbox.
 * - string[]                $selectedGroups Currently selected group values (optional)
 */

if (!isset($id)) :
    throw new Exception('grouped-multiselect snippet: $id not provided');
endif;

if (!isset($name)) :
    throw new Exception('grouped-multiselect snippet: $name not provided');
endif;

if (!isset($groups) || !is_array($groups)) :
    throw new Exception('grouped-multiselect snippet: $groups not provided');
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

if (!isset($groupName)) :
    $groupName = '';
endif;

if (!isset($selectedGroups) || !is_array($selectedGroups)) :
    $selectedGroups = [];
endif;

// Drop empty groups so we never render an empty section.
$groups = array_filter($groups, static fn($options): bool => !empty($options));

if (empty($groups)) :
    return;
endif;

$selectedCount = count($selectedValues) + count($selectedGroups);
$allChecked = $selectedCount === 0;
?>
<div class="container bg-light p-2 rounded my-2" id="<?= $id ?>" data-role="grouped-multiselect">
    <?php if ($label !== '') : ?>
    <p class="fw-bold mb-2"><?= htmlspecialchars($label) ?> <span class="badge text-bg-success<?= $allChecked ? ' d-none' : '' ?>" data-role="selected-count"><?= $selectedCount ?></span></p>
    <?php endif ?>

    <div class="form-check form-check--flush">
        <input type="checkbox" class="form-check-input" id="<?= $id ?>_all" data-role="all" <?= $allChecked ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= $id ?>_all"><?= htmlspecialchars($allLabel) ?></label>
    </div>

    <div data-role="options" class="mt-2<?= $allChecked ? ' d-none' : '' ?>">
        <input
            type="text"
            class="form-control form-control-sm mb-2"
            data-role="filter"
            placeholder="Type to filter&hellip;"
            aria-label="Filter <?= htmlspecialchars($label !== '' ? $label : 'options') ?>"
            autocomplete="off"
        >

        <div class="list-group list-group-flush" data-role="groups">
            <?php $groupIndex = 0;
            foreach ($groups as $groupLabel => $options) :
                $groupSelected = array_values(array_intersect($options, $selectedValues));
                $groupIsOpen = !empty($groupSelected);
                $panelId = $id . '_group_' . $groupIndex; ?>
            <div class="list-group-item px-0 py-1 border-0 bg-transparent" data-role="group">
                <div class="d-flex align-items-center gap-2">
                    <?php if ($groupName !== '') : ?>
                    <input
                        type="checkbox"
                        class="form-check-input flex-shrink-0 mt-0"
                        name="<?= $groupName ?>[]"
                        id="<?= $panelId ?>_group_check"
                        value="<?= htmlspecialchars((string) $groupLabel) ?>"
                        data-role="group-check"
                        aria-label="Select all of <?= htmlspecialchars((string) $groupLabel) ?>"
                        <?= in_array($groupLabel, $selectedGroups, true) ? 'checked' : '' ?>
                    >
                    <?php endif ?>
                    <button
                        type="button"
                        class="btn btn-light btn-sm flex-grow-1 d-flex justify-content-between align-items-center text-start border<?= $groupIsOpen ? '' : ' collapsed' ?>"
                        data-role="group-toggle"
                        aria-expanded="<?= $groupIsOpen ? 'true' : 'false' ?>"
                        aria-controls="<?= $panelId ?>"
                    >
                        <span class="fw-medium"><?= htmlspecialchars((string) $groupLabel) ?></span>
                        <span class="d-flex align-items-center">
                            <span class="badge text-bg-success me-2<?= empty($groupSelected) ? ' d-none' : '' ?>" data-role="group-count"><?= count($groupSelected) ?></span>
                            <span class="grouped-multiselect__chevron" aria-hidden="true">&#9662;</span>
                        </span>
                    </button>
                </div>
                <div id="<?= $panelId ?>" class="mt-1 ps-4<?= $groupIsOpen ? '' : ' d-none' ?>" data-role="group-panel">
                    <div class="mb-1">
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-role="group-select-all"><?= count($groupSelected) === count($options) ? 'Clear all' : 'Select all' ?></button>
                    </div>
                    <?php $optionIndex = 0;
                    foreach ($options as $option) :
                        $checkboxId = $panelId . '_' . $optionIndex; ?>
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
            <?php $groupIndex++;
            endforeach ?>
        </div>
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
.grouped-multiselect__chevron {
    display: inline-block;
    transition: transform 0.15s ease-in-out;
    transform: rotate(180deg); /* expanded: caret points up */
    font-size: 0.8rem;
    line-height: 1;
    opacity: 0.7;
}
[data-role="group-toggle"].collapsed .grouped-multiselect__chevron {
    transform: rotate(0deg); /* collapsed: caret points down, inviting expansion */
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

    // Keep the group badge (checked vice-county options), the per-group select-all
    // label, and the overall badge (all selections, including whole-group checks) in
    // step with the checkboxes.
    function refreshCounts() {
        root.querySelectorAll('[data-role="group"]').forEach(function (group) {
            const options = group.querySelectorAll('[data-role="option"] input[type="checkbox"]');
            const checkedOptions = group.querySelectorAll('[data-role="option"] input[type="checkbox"]:checked').length;
            const badge = group.querySelector('[data-role="group-count"]');
            if (badge) {
                badge.textContent = checkedOptions;
                badge.classList.toggle('d-none', checkedOptions === 0);
            }
            const selectAll = group.querySelector('[data-role="group-select-all"]');
            if (selectAll) {
                selectAll.textContent = (options.length > 0 && checkedOptions === options.length) ? 'Clear all' : 'Select all';
            }
        });
        const total = root.querySelectorAll('input[type="checkbox"][name]:checked').length;
        if (overallBadge) {
            overallBadge.textContent = total;
            overallBadge.classList.toggle('d-none', total === 0);
        }
        return total;
    }

    // Select-all / clear-all of the vice-county options within a single group.
    root.querySelectorAll('[data-role="group-select-all"]').forEach(function (button) {
        button.addEventListener('click', function () {
            const group = button.closest('[data-role="group"]');
            const options = group.querySelectorAll('[data-role="option"] input[type="checkbox"]');
            const shouldCheck = !Array.from(options).every(function (cb) { return cb.checked; });
            options.forEach(function (cb) { cb.checked = shouldCheck; });
            const total = refreshCounts();
            if (allCheckbox && total > 0) {
                allCheckbox.checked = false;
            }
        });
    });

    // "All" toggle: checking it clears every selection and hides the group list.
    if (allCheckbox) {
        allCheckbox.addEventListener('change', function () {
            if (allCheckbox.checked) {
                root.querySelectorAll('input[type="checkbox"][name]').forEach(function (cb) {
                    cb.checked = false;
                });
                refreshCounts();
            }
            optionsWrap.classList.toggle('d-none', allCheckbox.checked);
        });
    }

    // Expand / collapse a group.
    root.querySelectorAll('[data-role="group-toggle"]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const panel = document.getElementById(toggle.getAttribute('aria-controls'));
            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isOpen));
            toggle.classList.toggle('collapsed', isOpen);
            if (panel) {
                panel.classList.toggle('d-none', isOpen);
            }
        });
    });

    // Selecting any option means we are no longer in the "All" state.
    root.addEventListener('change', function (event) {
        if (event.target.matches('input[type="checkbox"][name]')) {
            const total = refreshCounts();
            if (allCheckbox && total > 0) {
                allCheckbox.checked = false;
            }
        }
    });

    // Client-side filter: show matching options and open groups that contain a match.
    const filter = root.querySelector('[data-role="filter"]');
    if (filter) {
        filter.addEventListener('input', function () {
            const term = filter.value.trim().toLowerCase();
            root.querySelectorAll('[data-role="group"]').forEach(function (group) {
                let anyVisible = false;
                group.querySelectorAll('[data-role="option"]').forEach(function (option) {
                    const match = term === '' || option.getAttribute('data-label').indexOf(term) !== -1;
                    option.classList.toggle('d-none', !match);
                    if (match) {
                        anyVisible = true;
                    }
                });
                group.classList.toggle('d-none', !anyVisible);

                // While filtering, open matching groups; restore collapsed state when cleared.
                const toggle = group.querySelector('[data-role="group-toggle"]');
                const panel = group.querySelector('[data-role="group-panel"]');
                if (term !== '' && panel) {
                    panel.classList.remove('d-none');
                } else if (panel && toggle) {
                    const stillOpen = toggle.getAttribute('aria-expanded') === 'true';
                    panel.classList.toggle('d-none', !stillOpen);
                }
            });
        });
    }
})();
</script>
