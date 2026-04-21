<?php

declare(strict_types=1);

/**
 * filteredfiles panel section.
 *
 * A configurable panel section for browsing and filtering Kirby files
 * attached to a page, with dropdown filters, freetext search, column
 * sorting, and pagination. Filter options, columns, and sort fields are
 * all declared in the blueprint.
 *
 * Supported filter types:
 *  - 'distinctText': exact match against a single text field (e.g. photographer).
 *                    Options are collected from distinct non-empty field values.
 *  - 'tags':         value-in-array match against a comma-separated tags field.
 *                    Options are collected from all distinct tag values.
 *  - 'pages':        value-in-array match against linked page IDs.
 *                    Options are loaded from a named Kirby collection.
 *
 * Blueprint example:
 * <code>
 * mySection:
 *   type: filteredfiles
 *   headline: Image Bank
 *   search: true
 *   pageSize: 25
 *   sortBy: filename asc
 *   sortOptions:
 *     - field: filename
 *       label: Filename
 *     - field: photographer
 *       label: Photographer
 *   filters:
 *     photographer:
 *       label: Photographer
 *       type: distinctText
 *     tags:
 *       label: Tag
 *       type: tags
 *     taxa:
 *       label: Taxon
 *       type: pages
 *       collection: uncachedTaxa
 *   columns:
 *     - field: filename
 *       label: Filename
 *       width: 1/2
 *     - field: photographer
 *       label: Photographer
 *       width: 1/2
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
        'headline' => function (string $headline = 'Files'): string {
            return $headline;
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
         * Default sort as "field dir", e.g. "filename asc".
         *
         * @param string $sortBy
         * @return string
         */
        'sortBy' => function (string $sortBy = 'filename asc'): string {
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
         * Filter definitions keyed by field name. Each entry needs 'label' and 'type'.
         * For type 'pages', also provide 'collection'.
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

        /**
         * File template to use when uploading via the Add button.
         *
         * @param string $uploadTemplate
         * @return string
         */
        'uploadTemplate' => function (string $uploadTemplate = 'image'): string {
            return $uploadTemplate;
        },

        /**
         * API endpoint prefix for options and results requests.
         *
         * Defaults to 'filtered-files', giving routes filtered-files/options and
         * filtered-files/results. Override (e.g. 'image-bank-panel') to point the
         * Vue component at a site-specific index-backed implementation.
         *
         * @param string $apiEndpoint
         * @return string
         */
        'apiEndpoint' => function (string $apiEndpoint = 'filtered-files'): string {
            return $apiEndpoint;
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

        /**
         * Resolved API endpoint prefix passed through to the Vue component.
         *
         * @return string
         */
        'resolvedApiEndpoint' => function (): string {
            return $this->apiEndpoint;
        },
    ],
];
