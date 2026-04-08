<?php

/**
 * Translated Pages Panel Section
 *
 * Displays a list of existing non-default language translations for the
 * current page, with clickable links to the page in the panel.
 * Returns an empty array when no translations exist so the Vue component
 * renders nothing and the Template Name section appears first.
 */

return [
    'computed' => [
        /**
         * Returns an array of existing non-default language translations
         * for the current page.
         *
         * Each entry contains:
         *   - code     (string) The language code, e.g. 'cy'
         *   - name     (string) The human-readable language name, e.g. 'Welsh'
         *   - panelUrl (string) The panel edit URL for this page
         *
         * @return array<int, array{code: string, name: string, panelUrl: string}>
         */
        'translations' => function () {
            try {
                $page = $this->model();
                $nonDefaultTranslations = [];
                $defaultLanguage = null;

                foreach ($this->kirby()->languages() as $language) {
                    if ($language->isDefault()) {
                        $defaultLanguage = $language;
                        continue;
                    }

                    if ($page->translation($language->code())->exists()) {
                        $nonDefaultTranslations[] = [
                            'code'     => $language->code(),
                            'name'     => $language->name(),
                            'panelUrl' => $page->panel()->url() . '?language=' . $language->code(),
                        ];
                    }
                }

                if (empty($nonDefaultTranslations)) {
                    return [];
                }

                $translations = [];

                if ($defaultLanguage !== null) {
                    $translations[] = [
                        'code'     => $defaultLanguage->code(),
                        'name'     => $defaultLanguage->name(),
                        'panelUrl' => $page->panel()->url() . '?language=' . $defaultLanguage->code(),
                    ];
                }

                return array_merge($translations, $nonDefaultTranslations);
            } catch (Throwable $e) {
                error_log('Failed to load translated pages section: ' . $e->getMessage());
                return [];
            }
        }
    ],
];
