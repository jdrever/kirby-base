<?php

declare(strict_types=1);

/**
 * filteredpages panel section.
 *
 * A generic, configurable replacement for Kirby's native pages section that
 * adds dropdown filters, freetext search, column sorting, and pagination.
 * Filter options, columns, and sort fields are all declared in the blueprint.
 *
 * Blueprint example:
 * <code>
 * mySection:
 *   type: filteredpages
 *   headline: Events
 *   template: event
 *   search: true
 *   pageSize: 25
 *   sortBy: startDate desc
 *   sortOptions:
 *     - field: startDate
 *       label: Start Date
 *     - field: title
 *       label: Title
 *   filters:
 *     activities:
 *       label: Activity
 *       type: pages
 *       collection: activities
 *     projects:
 *       label: Project
 *       type: pages
 *       collection: projectsAll
 *   columns:
 *     - field: title
 *       label: Page Name
 *       width: 1/2
 *     - field: status
 *       label: Status
 *       width: 1/4
 *     - field: startDate
 *       label: Start Date
 *       width: 1/4
 * </code>
 */
return [
    'props' => [
        /**
         * Section headline displayed above the toolbar.
         *
         * @param string $headline
         * @return string
         */
        'headline' => function (string $headline = 'Pages'): string {
            return $headline;
        },

        /**
         * Intended template used to filter child pages.
         *
         * @param string $template
         * @return string
         */
        'template' => function (string $template = ''): string {
            return $template;
        },

        /**
         * Number of items per page.
         *
         * @param int $pageSize
         * @return int
         */
        'pageSize' => function (int $pageSize = 25): int {
            return max(1, $pageSize);
        },

        /**
         * Whether to show a freetext search input.
         *
         * @param bool $search
         * @return bool
         */
        'search' => function (bool $search = false): bool {
            return $search;
        },

        /**
         * Default sort as "field dir", e.g. "startDate desc".
         *
         * @param string $sortBy
         * @return string
         */
        'sortBy' => function (string $sortBy = 'title asc'): string {
            return $sortBy;
        },

        /**
         * Fields the user can click to re-sort. Each entry has 'field' and 'label'.
         *
         * @param array<int, array{field: string, label: string}> $sortOptions
         * @return array<int, array{field: string, label: string}>
         */
        'sortOptions' => function (array $sortOptions = []): array {
            return $sortOptions;
        },

        /**
         * Filter definitions keyed by field name. Each entry needs 'label', 'type',
         * and (for type 'pages') 'collection'.
         *
         * @param array<string, array<string, mixed>> $filters
         * @return array<string, array<string, mixed>>
         */
        'filters' => function (array $filters = []): array {
            return $filters;
        },

        /**
         * Column definitions. Each entry needs 'field', 'label', and optionally 'width'.
         *
         * @param array<int, array<string, mixed>> $columns
         * @return array<int, array<string, mixed>>
         */
        'columns' => function (array $columns = []): array {
            return $columns;
        },
    ],

    'computed' => [
        /**
         * Kirby page ID of the section's parent page, used to scope API calls.
         *
         * @return string
         */
        'modelId' => function (): string {
            return $this->model()->id();
        },

    ],
];
