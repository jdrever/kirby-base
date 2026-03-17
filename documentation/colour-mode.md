# Colour Mode

Kirby-base provides a Bootstrap-compatible light/dark/auto colour mode system with no flash of unstyled content (FOUC).

## How it works

Everything runs from a single inline script in `<head>`. There is no external JS file to load or `addScript()` call needed in consuming sites.

On every page load the script:

1. Reads the `colourMode` cookie (`light`, `dark`, or `auto`; defaults to `auto` if absent)
2. Resolves `auto` against the OS `prefers-color-scheme` media query
3. Sets `data-bs-theme` on `<html>` **before the page renders**, preventing any flash of the wrong theme
4. Listens for OS preference changes and re-applies the theme when the stored mode is `auto`
5. Once the DOM is ready, hydrates the selector UI (bolds and disables the active button) and wires up click handlers

The preference is stored in a cookie (not `localStorage`) so it is available server-side and survives private-browsing sessions.

## Snippets

### `colour-mode/script`

Included automatically by `base/header`. Inlines the full colour-mode JS in `<head>`.

No action required in consuming sites — it is registered in `snippets.php` and called from `header.php`.

### `colour-mode/selector`

Outputs three buttons (Light / Dark / Auto). Include it wherever you want the toggle to appear, typically in a nav bar:

```php
<?php snippet('colour-mode/selector') ?>
```

Rendered HTML:

```html
<span class="colour-mode-selector">
    <button type="button" class="colour-mode-btn" data-colour-mode="light">Light</button>
    |
    <button type="button" class="colour-mode-btn" data-colour-mode="dark">Dark</button>
    |
    <button type="button" class="colour-mode-btn" data-colour-mode="auto">Auto</button>
</span>
```

The active button is bolded and disabled by the inline script once the DOM is ready.

### `colour-mode/tag`

Outputs the `data-bs-theme` attribute on `<html>`. Used inside `base/header`:

```html
<html lang="en" data-bs-theme="auto">
```

The static value `auto` is replaced immediately by the inline script, so it only appears in source if JS is disabled.

## CSS

Bootstrap 5.3+ reads `data-bs-theme` natively. No additional CSS is required for light/dark switching.

To style the selector buttons, target `.colour-mode-selector` and `.colour-mode-btn`.

## Cookie

| Property | Value |
|----------|-------|
| Name | `colourMode` |
| Values | `light`, `dark`, `auto` |
| Expiry | 365 days |
| Path | `/` |
| SameSite | `Lax` |
