<?php

declare(strict_types=1);

/**
 * filearchivelinks panel section.
 *
 * Shows, for the current file, every page on the site that links to it — using
 * the FileLinkIndexHelper reverse-link index. Intended for file blueprints (e.g.
 * the File Archive) so editors can see where a file is used before changing or
 * removing it, avoiding dead links.
 *
 * Blueprint usage:
 * <code>
 * linkedFrom:
 *   type: filearchivelinks
 *   headline: Linked from these pages
 *   emptyText: No pages link to this file.
 * </code>
 *
 * The section is file-type agnostic (it matches purely on the file's UUID), so it
 * is also used on the generic image blueprint — pass an image-specific
 * headline/emptyText there.
 */

use BSBI\WebBase\helpers\FileLinkIndexHelper;
use BSBI\WebBase\helpers\KirbyInternalHelper;

return [
    'props' => [
        /**
         * Section headline.
         *
         * @param string $headline
         * @return string
         */
        'headline' => function (string $headline = 'Linked from'): string {
            return $headline;
        },

        /**
         * Message shown when the index is built but no pages reference the file.
         *
         * Configurable so the section can present file-type-appropriate wording
         * (e.g. "No pages use this image." on Image Bank items).
         *
         * @param string $emptyText
         * @return string
         */
        'emptyText' => function (string $emptyText = 'No pages link to this file.'): string {
            return $emptyText;
        },

        /**
         * Whether the file-link index has been built yet.
         *
         * @return bool
         */
        'indexReady' => function (): bool {
            return FileLinkIndexHelper::isIndexReady();
        },
    ],
    'computed' => [
        /**
         * Pages that link to the current file.
         *
         * Resolves each indexed page ID (including drafts) to its title and Panel
         * URL for click-through. Returns an empty list when the index has not been
         * built or the file has no UUID.
         *
         * @return array<int, array{pageId: string, title: string, panelUrl: string|null, url: string|null, linkTypes: string}>
         */
        'links' => function (): array {
            try {
                if (!FileLinkIndexHelper::isIndexReady()) {
                    return [];
                }

                $uuid = $this->model()->uuid()?->id();
                if ($uuid === null) {
                    return [];
                }

                $index  = new FileLinkIndexHelper();
                $helper = new KirbyInternalHelper();

                $links = [];
                foreach ($index->getLinkingPages($uuid) as $row) {
                    $page = $helper->findKirbyPageOrDraft($row['pageId']);
                    $links[] = [
                        'pageId'    => $row['pageId'],
                        'title'     => $page?->title()->value() ?? $row['pageId'],
                        'panelUrl'  => $page?->panel()->url(),
                        'url'       => $page?->url(),
                        'linkTypes' => $row['linkTypes'],
                    ];
                }
                return $links;
            } catch (Throwable $e) {
                error_log('Failed to load file archive links: ' . $e->getMessage());
                return [];
            }
        },
    ],
];
