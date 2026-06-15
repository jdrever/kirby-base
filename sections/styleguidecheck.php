<?php

declare(strict_types=1);

use Kirby\Cms\Page;

/**
 * Style Guide Check Panel Section
 *
 * Displays a button that sends the current page's text content to the
 * Gemini API for checking against the site's style guide
 * (site/config/style-guide.md), then shows the report inline.
 */
return [
    'props' => [
        /**
         * Section heading displayed above the button.
         *
         * @param string $headline
         * @return string
         */
        'headline' => function (string $headline = 'Style Guide Check'): string {
            return $headline;
        },
    ],
    'computed' => [
        /**
         * The Kirby page ID of the current model, passed to the Vue component
         * so it can send the correct page ID to the API route.
         *
         * @return string
         */
        'pageId' => function (): string {
            $model = $this->model();
            return $model instanceof Page ? $model->id() : '';
        },
    ],
];
