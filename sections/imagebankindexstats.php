<?php

/**
 * Image Bank Index Stats Panel Section
 *
 * Displays statistics for the imageBank SQLite index,
 * including file/taxa counts and last rebuild timestamp.
 */

use BSBI\WebBase\helpers\ImageBankIndexHelper;

return [
    'props' => [
        /**
         * Section headline.
         *
         * @param string $headline
         * @return string
         */
        'headline' => function (string $headline = 'Image Bank Index'): string {
            return $headline;
        },
    ],
    'computed' => [
        /**
         * Returns stats for the imageBank SQLite index, or null if the
         * index has not yet been built.
         *
         * @return array{total_files: int, total_taxa: int, last_rebuild: string|null}|null
         */
        'stats' => function (): ?array {
            try {
                if (!ImageBankIndexHelper::isIndexReady()) {
                    return null;
                }
                $helper = new ImageBankIndexHelper();
                return $helper->getStats();
            } catch (Throwable $e) {
                error_log('Failed to load imageBank index stats: ' . $e->getMessage());
                return null;
            }
        },
    ],
];
