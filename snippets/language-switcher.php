<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


use BSBI\WebBase\models\BaseWebPage;

if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

/**
 * @var BaseWebPage $currentPage
 */

$languages = $currentPage->getLanguages();

if (!$languages->isEnabled()) :
    return;
endif;

//TODO: allow switcher to always display based on site config
if ($languages->isPageTranslatedInCurrentLanguage()) : ?>
<nav class="language-switcher bg-body border border-warning rounded my-3" aria-label="Language Selector">
    <ul class="nav nav-pills justify-content-center p-2 rounded">
    <?php foreach ($languages->getLanguages() as $language) : ?>
        <li class="nav-item mx-1">
            <a href="<?= $language->getCurrentPageUrl() ?>"
            hreflang="<?= $language->getCode() ?>"
            class="nav-link <?= $language->isActivePage() ? 'active text-white' : 'text-body' ?>"
            <?= $language->isActivePage() ? 'aria-current="page"' : '' ?>>
            <?= html($language->getName()) ?>
            </a>
        </li>
    <?php endforeach ?>
    </ul>
</nav>
<?php else :
    if (!$languages->isUsingDefaultLanguage()) :
        snippet('translations/no-'.strtolower($languages->getCurrentLanguage()));
    endif;
endif ?>