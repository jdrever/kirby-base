<?php

/**
 * Search Index Stats Panel Section
 *
 * Displays statistics for the search index,
 * including page count and last rebuild timestamp,
 * with a rebuild button.
 */

use BSBI\WebBase\helpers\SearchIndexHelper;

return [
    'props' => [
        'headline' => function ($headline = 'Search Index') {
            return $headline;
        }
    ],
    'computed' => [
        'stats' => function () {
            try {
                $searchIndex = new SearchIndexHelper();
                return $searchIndex->getStats();
            } catch (Throwable $e) {
                error_log('Failed to load search index stats: ' . $e->getMessage());
                return [
                    'total_pages' => 0,
                    'last_rebuild' => null,
                ];
            }
        }
    ],
];
