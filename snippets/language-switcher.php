<?php
/**
 * Kirby CMS Language Switcher Snippet
 *
 * This snippet checks if the current page has translated content
 * in languages other than the default one and provides a language switcher.
 *
 * To use this:
 * 1. Ensure you have multiple languages configured in your `config.php` (or `config/*.php`).
 * 2. Include this snippet in your header or footer, or wherever you want the switcher to appear.
 * Example: `<?php snippet('language-switcher') ?>` if saved as `site/snippets/language-switcher.php`
 *
 * Note: This assumes your content files are structured with language codes (e.g., `about.en.txt`, `about.de.txt`).
 */

// Get the current site object
$site = kirby()->site();

// Get all configured languages
$languages = kirby()->languages();

// Get the default language
$defaultLanguage = kirby()->defaultLanguage();

if ($defaultLanguage !== null) :

// Get the current page
$currentPage = $page;

// Initialize an array to store available language URLs for the current page
$availableLanguageUrls = [];

// Loop through all configured languages
foreach ($languages as $lang) {
    // Skip the default language, as we're looking for *other* languages
    // or if the current language is the one we're checking
    if ($lang->code() === $defaultLanguage->code()) {
        continue;
    }

    $translatedPage = $currentPage->translation($lang->code());

    // Check if the PageTranslation object exists AND if its content is not empty.
    // The `exists()` method on PageTranslation checks if the content file exists.
    // The `content()->isNotEmpty()` checks if there are actual fields with values in that content file.
    if ($translatedPage && $translatedPage->exists()) {
        // Add the language code and its URL to our list
        $availableLanguageUrls[$lang->code()] = $translatedPage->parent()->url($lang->code());
    }
}

// Only show the language switcher if there are available translations
if (!empty($availableLanguageUrls)) :
    ?>
    <nav class="language-switcher my-3" aria-label="Language Selector">
        <ul class="nav nav-pills justify-content-center bg-light p-2 rounded">
            <?php foreach ($languages as $lang) : ?>
                <?php
                // Get the URL for the current page in this language
                $langUrl = $currentPage->url($lang->code());
                // Determine if this is the active language
                $isActive = ($lang->code() === kirby()->language()->code());
                ?>
                <li class="nav-item mx-1">
                    <a href="<?= $langUrl ?>"
                       hreflang="<?= $lang->code() ?>"
                       class="nav-link <?= $isActive ? 'active' : '' ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>>
                        <?= html($lang->name()) ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </nav>
<?php else:
    if ($defaultLanguage->code() !== kirby()->language()->code()) : ?>
    <div class="alert alert-warning" role="alert">This page hasn't been translated into <?=kirby()->language()->name() ?>.</div>
    <?php endif;
endif;
endif;
?>