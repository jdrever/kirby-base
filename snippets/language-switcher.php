<?php

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
<nav class="language-switcher my-3" aria-label="Language Selector">
    <ul class="nav nav-pills justify-content-center bg-light p-2 rounded">
    <?php foreach ($languages->getLanguages() as $language) : ?>
        <li class="nav-item mx-1">
            <a href="<?= $language->getCurrentPageUrl() ?>"
            hreflang="<?= $language->getCode() ?>"
            class="nav-link <?= $language->isActivePage() ? 'active' : '' ?>"
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