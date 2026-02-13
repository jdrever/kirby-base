# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**kirby-base** (`open-foundations/kirby-base`) is a Kirby CMS v5 plugin (v1.6.58) that provides reusable base classes
, blueprints, snippets, and helpers for building websites. It is installed as a git submodule into consuming sites at 
`site/plugins/kirby-base`.

## Commands

- **Install dependencies:** `composer install`
- **Update dependencies:** `composer update`
- **Run tests:** `vendor/bin/phpunit` (all tests) or `vendor/bin/phpunit tests/Unit/models/UserTest.php` (single file)
- **Lint (PHP CodeSniffer):** `vendor/bin/phpcs` — enforces PSR-12 + Slevomat standards on the `site/` directory
- **Fix lint issues:** `vendor/bin/phpcbf`

There is no JavaScript build process or CSS preprocessor configured in this project.  They would be supplied by the web 
application using the plugin.

## Architecture

### Plugin Registration

`index.php` registers the plugin via `Kirby::plugin()`, pulling in configuration from separate files:
- `blueprints.php` — blueprint registration
- `snippets.php` — snippet registration
- `hooks.php` — lifecycle hooks
- `routes.php` — custom routes (sitemap.xml, robots.txt, etc.)

### PHP Classes (namespace: `BSBI\WebBase\`)

PSR-4 autoloaded from `classes/`. The class hierarchy is:

- **`BaseModel`** — foundation for all models, uses `ErrorHandling` and `OptionsHandling` traits
- **`BaseWebPage`** extends `BaseModel` — core page model with properties for menus, SEO, permissions, etc.
- **`BaseList`** / **`BaseFilter`** — collection and filtering base classes with pagination support
- **`KirbyBaseHelper`** (`classes/helpers/`) — large helper class (~6k lines) that bridges Kirby CMS data to 
model objects. Consuming sites extend this and implement `getBasicPage`/`setBasicPage`.
- **`SearchIndexHelper`** — SQLite FTS5 full-text search implementation
- **13 traits** in `classes/traits/` provide mixins for concerns like `GenericKirbyHelper`, `FormProperties`, 
`ImageHandling`, `LoginProperties`, etc.

### Extension Pattern

Consuming sites are expected to:
1. Create a `WebPage` class extending `BaseWebPage` with site-specific fields
2. Create a `KirbyHelper` class extending `KirbyBaseHelper`, implementing `getBasicPage()` and `setBasicPage()`
3. Optionally create a `CoreLinkType` enum for typed access to core navigation links

### Blueprints (`blueprints/`)

YAML files defining Kirby Panel UI structure: blocks (17 types), fields, files, layouts, pages, sections, and tabs. 
These are registered in `blueprints.php`.

### Snippets (`snippets/`)

62+ PHP template partials organized by concern: `base/` (header, footer, menu), `blocks/`, `form/`, `search/`, 
`colour-mode/`, `feedback/`, `user-status/`.

### Key Features

- **Search:** SQLite FTS5 search with configurable field weights, stop words, and optional Panel search override 
(enabled via `search.panelSearch` config option)
- **Forms:** Form builder with CSRF and Cloudflare Turnstile CAPTCHA support; submissions stored as Kirby pages
- **File archive:** Permanent URLs for downloadable files via `file_link` controller/template
- **Authentication:** Role-based access control and per-page password protection via `permissions` tab blueprint
- **Image handling:** Responsive images with srcset generation, WebP conversion, and image bank support

## Coding Standards

- PHP 8.3+ required (`declare(strict_types=1)` everywhere)
- PSR-12 code style with Slevomat enhancements: full type hints on parameters, returns, and properties
- PascalCase classes, camelCase methods/properties, snake_case for Kirby template/blueprint names

## Version Management

To release a new version: update `version` in `composer.json`, commit, tag with the version number, and push with tags.
