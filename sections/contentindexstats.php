<?php

/**
 * Content Index Stats Panel Section
 *
 * Displays statistics for all registered content indexes,
 * including row counts and last rebuild timestamps.
 */

use BSBI\WebBase\helpers\ContentIndexRegistry;

return [
    'props' => [
        'headline' => function ($headline = 'Content Index') {
            return $headline;
        }
    ],
    'computed' => [
        'indexes' => function () {
            $stats = [];
            try {
                foreach (ContentIndexRegistry::all() as $manager) {
                    $stats[] = $manager->getStats();
                }
            } catch (Throwable $e) {
                error_log('Failed to load content index stats: ' . $e->getMessage());
            }
            return $stats;
        }
    ],
];
