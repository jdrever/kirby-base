<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


if (!isset($currentPage)) :
    throw new Exception('$currentPage not provided');
endif;

$languages = $currentPage->getLanguages();

if ($languages->isEnabled()
    && !$languages->isUsingDefaultLanguage()
    && !$languages->isPageTranslatedInCurrentLanguage()) : ?>
<meta name="robots" content="noindex, follow">
<?php endif ?>
