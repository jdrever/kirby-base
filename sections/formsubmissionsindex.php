<?php

declare(strict_types=1);

/**
 * Form Submissions Index Panel Section
 *
 * Displays a summary of all form_submission pages across the entire site,
 * grouped by form_type, with per-type submission counts and CSV export links.
 *
 * Intended for the site-level dashboard, not individual form pages.
 */
return [
    'props' => [
        'headline' => function (string $headline = 'Form Submissions') {
            return $headline;
        }
    ],
    'computed' => [
        /**
         * All form_submission pages grouped by form_type, with counts and
         * per-type export URLs.
         *
         * @return array<int, array{formType: string, count: int, exportUrl: string}>
         */
        'formTypes' => function (): array {
            $submissions = site()->index()->filterBy('template', 'form_submission');

            $counts = [];
            foreach ($submissions as $submission) {
                $formType = (string) $submission->form_type()->value();
                if ($formType === '') {
                    $formType = '(untyped)';
                }
                $counts[$formType] = ($counts[$formType] ?? 0) + 1;
            }

            $baseUrl = kirby()->url() . '/form-export-all';
            $result  = [];
            foreach ($counts as $formType => $count) {
                $result[] = [
                    'formType'  => $formType,
                    'count'     => $count,
                    'exportUrl' => $baseUrl . '?form_type=' . urlencode($formType),
                ];
            }

            usort($result, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);

            return $result;
        },

        /**
         * Total submission count across all form types.
         */
        'totalCount' => function (): int {
            return site()->index()->filterBy('template', 'form_submission')->count();
        },

        /**
         * URL to export all submissions regardless of form type.
         */
        'exportAllUrl' => function (): string {
            return kirby()->url() . '/form-export-all';
        },
    ],
];
