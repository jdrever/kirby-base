<?php

declare(strict_types=1);

/**
 * Form Submission Export Panel Section
 *
 * Displays a submission count and a download button on form pages.
 * The button links to the form-export route which streams a CSV file
 * containing all form_submission child pages as rows.
 */
return [
    'props' => [
        'headline' => function (string $headline = 'Export Submissions') {
            return $headline;
        }
    ],
    'computed' => [
        /**
         * Count of form_submission child pages under the current page.
         */
        'submissionCount' => function (): int {
            return $this->model()->children()->template('form_submission')->count();
        },
        /**
         * URL of the CSV export route for the current page.
         */
        'exportUrl' => function (): string {
            return kirby()->url() . '/form-export/' . $this->model()->id();
        }
    ]
];
