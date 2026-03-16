# Cookie Consent

Kirby-base provides a cookie consent system with a dismissable banner, consent-gated content blocks, and a small JS API for checking status.

## How it works

`cookie-consent.js` is loaded as an external file at the end of `<body>`. On each page it:

1. Reads the `cookieConsent` cookie (`accepted` or `rejected`; falls back to the legacy `cookieConsentGiven` cookie)
2. Hides the banner if a choice has already been recorded
3. Shows placeholders and hides consent-gated content blocks if consent has not been accepted
4. Hides the "Reject" button in placeholders if the user has already rejected
5. Wires up Accept/Reject click handlers on all `[data-consent-accept]` and `[data-consent-reject]` buttons
6. Exposes `window.cookieConsent` for external use

## Setup in a consuming site

Unlike colour mode, cookie consent requires two steps in the consuming site:

**1. Load the JS.** In your `KirbyHelper::setBasicPage()` call `addScript`:

```php
$currentPage->addScript('cookie-consent', self::ASSETS_PATH);
```

`ASSETS_PATH` should resolve to `/media/plugins/open-foundations/kirby-base/`.

**2. Include the banner snippet.** In your header, include a banner that uses the `data-cookie-consent-banner` attribute. Kirby-base does not ship a ready-made banner because the wording is site-specific, but the required data attributes are:

| Element | Attribute | Purpose |
|---------|-----------|---------|
| Banner wrapper | `data-cookie-consent-banner` | Hidden by JS once a choice is recorded |
| Accept button | `data-consent-accept` | Sets status to `accepted` |
| Reject button | `data-consent-reject` | Sets status to `rejected` |

Example:

```html
<div data-cookie-consent-banner>
  <p>We use cookies…</p>
  <button type="button" data-consent-accept>Accept</button>
  <button type="button" data-consent-reject>Reject</button>
</div>
```

## Gating content behind consent

Wrap content in a `[data-requires-consent]` container with a placeholder and the real content as siblings:

```html
<div data-requires-consent>
  <div data-consent-placeholder>
    <!-- shown until consent is accepted -->
    <p>This map requires your consent to cookies.</p>
    <button type="button" data-consent-accept>Accept cookies</button>
    <button type="button" data-consent-reject data-consent-reject-btn>Reject</button>
  </div>
  <div data-consent-content>
    <!-- hidden until consent is accepted -->
    <iframe src="…"></iframe>
  </div>
</div>
```

Kirby-base ships a `block-consent` snippet that renders the placeholder half:

```php
<?php snippet('block-consent', ['contentType' => 'map', 'purpose' => 'We use Google Maps to display location information.']) ?>
```

Parameters:

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$contentType` | `'content'` | Shown in "This `X` requires your consent" |
| `$purpose` | — | Optional additional explanation |

## JavaScript API

After the script loads, `window.cookieConsent` is available:

```js
window.cookieConsent.hasConsent()   // true if accepted
window.cookieConsent.hasRejected()  // true if rejected
window.cookieConsent.getStatus()    // 'accepted', 'rejected', or null
window.cookieConsent.setStatus('accepted') // programmatically accept
```

## Cookie

| Property | Value |
|----------|-------|
| Name | `cookieConsent` |
| Values | `accepted`, `rejected` |
| Expiry | 365 days |
| Path | `/` |
| SameSite | `Lax` |

Legacy cookie `cookieConsentGiven` (values `true`/`yes`/`1`/`no`) is read for backwards compatibility and deleted once a new choice is recorded.

---

## Comparison with colour mode — suggested improvements

Colour mode is entirely self-contained in kirby-base. Cookie consent currently requires more manual wiring in consuming sites. Three inconsistencies are worth addressing:

### 1. No auto-loading script snippet

Colour mode ships a `colour-mode/script` snippet that is included automatically by `base/header`, so consuming sites need zero setup for the JS. Cookie consent has no equivalent — sites must call `addScript('cookie-consent', ...)` manually.

**Suggested fix:** Add a `cookie-consent/script` snippet to kirby-base that either inlines or `<script src>`s the JS, and include it from `base/header` alongside `colour-mode/script`. The `addScript()` call in consuming sites' `KirbyHelper` can then be removed.

Unlike colour mode there is no FOUC concern for cookie consent, so inlining is optional — loading via `<script src>` at end of `<body>` is fine.

### 2. No ready-made banner snippet in kirby-base

Colour mode ships `colour-mode/selector` as a ready-to-use snippet. Cookie consent has no equivalent banner snippet in kirby-base — consuming sites must write their own HTML with the correct data attributes.

**Suggested fix:** Add a generic `cookie-consent/banner` snippet to kirby-base with sensible default wording and all the required data attributes pre-wired. Consuming sites can override it if they need site-specific text.

### 3. External JS file vs inline

Colour mode JS is inlined in `<head>` (so the theme is applied before the first paint). Cookie consent JS is an external file loaded at end of `<body>`, which means the banner can briefly appear before JS hides it.

**Suggested fix (lower priority):** Inline `cookie-consent.js` into a `cookie-consent/script` snippet (mirroring colour-mode), so the consent state is applied as early as possible and the banner flash is eliminated.
