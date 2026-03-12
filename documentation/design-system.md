# Applying the BSBI Design System to Another Site

## Prerequisites

- Node.js and npm installed
- Sass, Rollup, and PurgeCSS available (copy `package.json` from `bsbi-docs` and run `npm install`)

## Step 1 — Copy the build files

Copy these from `bsbi-docs/build/` to your new site's `build/` directory:

| File | Action |
|---|---|
| `_design-system.scss` | Copy unchanged |
| `custom.scss` | Copy unchanged |
| `bootstrap.js` | Copy unchanged |
| `_tokens.scss` | Copy and **customise** (see below) |

Also copy `rollup.config.mjs` and `purgecss.config.js` from the repo root, updating `purgecss.config.js` to point at your site's template directory.

## Step 2 — Customise `_tokens.scss`

This is the only file you need to edit. Update:

**Primary colours** — 4 shades of your brand colour (light mode), plus dark mode equivalents (roughly half the lightness):

```scss
$site-colour-primary:         #505A70;  // base
$site-colour-primary-light:   #838D9E;  // lighter
$site-colour-primary-dark:    #2B2D42;  // darker
$site-colour-primary-darkest: #272C37;  // darkest
```

**Logo** — URL, dimensions, and horizontal offset to align it within the header:

```scss
$site-logo-url:                   url('/assets/images/your-logo-white.svg');
$site-logo-width-mobile:          160px;
$site-logo-height-mobile:         68px;
$site-logo-width-desktop:         220px;
$site-logo-height-desktop:        94px;
$site-logo-main-header-padding-y: 1rem;
$site-logo-x-offset:              -28px;
```

## Step 3 — Copy assets

- **Fonts**: copy `src/assets/fonts/` (Source Sans 3 WOFF2 files) — or swap for your chosen typeface and update the `@font-face` rules in `_design-system.scss`
- **Logo**: add a white SVG version of your logo to `src/assets/images/`

## Step 4 — Build

```bash
npm run build
```

This runs: `sass` → `rollup` → `purgecss`, outputting the final CSS to `src/assets/css/custom.css`.

## What you get without any further changes

- Full Bootstrap 5.3 (layout, grid, utilities)
- Light/dark mode support
- Responsive typography and spacing
- All shared components (header, footer, nav, cards, buttons, blocks, etc.) styled in your brand colours

## What lives in `_design-system.scss` (don't edit)

The shared file contains all component styles, typography, Bootstrap configuration, and colour palette. Changes here propagate to all sites using the system. If you need to fix or add a component, do it there and re-copy to consuming sites.
