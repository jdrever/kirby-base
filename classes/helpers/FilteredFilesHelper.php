<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

use Kirby\Cms\File;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Data\Yaml;

/**
 * Generic helper for the filteredfiles panel section.
 *
 * Provides two groups of methods:
 *  - Pure static methods (applyFilters, applySearch, applySort, paginate) that
 *    operate on plain PHP arrays and can be unit-tested without a Kirby bootstrap.
 *  - Kirby-dependent methods (getOptions, getResults) that read live file data.
 *
 * File data arrays have the shape:
 * <code>
 * [
 *   'id'            => string,          // e.g. 'image-bank/photo.jpg'
 *   'title'         => string,          // imageTitle field or filename
 *   'filename'      => string,
 *   'panelUrl'      => string,
 *   'thumbUrl'      => string,
 *   'filterValues'  => [                // one key per configured filter
 *     'photographer' => 'John Smith',   // distinctText type: single string
 *     'tags'         => ['Fungi', ...], // tags type: array of strings
 *     'taxa'         => ['taxa/fungi'], // pages type: array of page IDs
 *   ],
 *   'displayValues' => [
 *     'title'        => string,
 *     'filename'     => string,
 *     'photographer' => string,
 *   ],
 * ]
 * </code>
 *
 * Supported filter types:
 *  - 'distinctText': exact match against a single text field value (e.g. photographer).
 *                    getOptions collects distinct non-empty values from all files.
 *  - 'tags':         value-in-array match against a comma-separated tags field.
 *                    getOptions collects all distinct tag values from all files.
 *  - 'pages':        value-in-array match against linked page IDs (same as FilteredPagesHelper).
 *                    getOptions loads a named Kirby collection.
 *
 * @package BSBI\WebBase\helpers
 */
class FilteredFilesHelper
{
    // ── Pure methods (no Kirby dependency) ────────────────────────────────

    /**
     * Filters files by the active dropdown selections.
     *
     * Each active filter value must match the corresponding filterValues entry
     * (AND logic across filters; empty/null values are skipped).
     *
     * Supported types: 'distinctText' (exact string match), 'tags' (value in array),
     * 'pages' (value in array of page IDs).
     *
     * @param array<int, array<string, mixed>>    $files      File data arrays.
     * @param array<string, array<string, mixed>> $filterDefs Filter definitions keyed by field name.
     * @param array<string, string>               $active     Selected values keyed by field name.
     * @return array<int, array<string, mixed>>
     */
    public static function applyFilters(array $files, array $filterDefs, array $active): array
    {
        $filtered = [];
        foreach ($files as $file) {
            $match = true;
            foreach ($active as $field => $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                if (!isset($filterDefs[$field])) {
                    continue;
                }
                $type       = $filterDefs[$field]['type'] ?? 'pages';
                $fileValues = $file['filterValues'][$field] ?? [];

                if ($type === 'distinctText') {
                    if ($fileValues !== $value) {
                        $match = false;
                        break;
                    }
                } elseif ($type === 'tags') {
                    if (!in_array($value, (array)$fileValues, true)) {
                        $match = false;
                        break;
                    }
                } elseif ($type === 'pages') {
                    if (!in_array($value, (array)$fileValues, true)) {
                        $match = false;
                        break;
                    }
                }
            }
            if ($match) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }

    /**
     * Filters files to those whose title contains the search string (case-insensitive).
     *
     * An empty search string returns all files unchanged.
     *
     * @param array<int, array<string, mixed>> $files  File data arrays.
     * @param string                           $search Search string.
     * @return array<int, array<string, mixed>>
     */
    public static function applySearch(array $files, string $search): array
    {
        if ($search === '') {
            return $files;
        }
        $lower = strtolower($search);
        return array_values(
            array_filter($files, fn(array $f) => str_contains(strtolower($f['title']), $lower))
        );
    }

    /**
     * Sorts files by a display field, ascending or descending.
     *
     * Sort value is resolved from displayValues[$field], falling back to
     * the top-level $file[$field] key. Comparison is lexicographic.
     *
     * @param array<int, array<string, mixed>> $files     File data arrays.
     * @param string                           $sortField Field name to sort by.
     * @param string                           $sortDir   'asc' or 'desc'.
     * @return array<int, array<string, mixed>>
     */
    public static function applySort(array $files, string $sortField, string $sortDir): array
    {
        usort($files, function (array $a, array $b) use ($sortField, $sortDir): int {
            $aVal = $a['displayValues'][$sortField] ?? $a[$sortField] ?? '';
            $bVal = $b['displayValues'][$sortField] ?? $b[$sortField] ?? '';
            $cmp  = strcmp((string)$aVal, (string)$bVal);
            return $sortDir === 'desc' ? -$cmp : $cmp;
        });
        return $files;
    }

    /**
     * Slices a flat array of files into a single paginated result set.
     *
     * @param array<int, array<string, mixed>> $files    Full (pre-filtered) files array.
     * @param int                              $page     1-based page number.
     * @param int                              $pageSize Items per page.
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, pageSize: int, totalPages: int}
     */
    public static function paginate(array $files, int $page, int $pageSize): array
    {
        $total      = count($files);
        $offset     = ($page - 1) * $pageSize;
        $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 0;

        return [
            'items'      => array_values(array_slice($files, $offset, $pageSize)),
            'total'      => $total,
            'page'       => $page,
            'pageSize'   => $pageSize,
            'totalPages' => $totalPages,
        ];
    }

    // ── Kirby-dependent methods ────────────────────────────────────────────

    /**
     * Returns available options for each configured filter dropdown.
     *
     * Supports filter type 'pages': loads the named Kirby collection and
     * returns [{value: pageId, text: pageTitle}, ...].
     *
     * Supports filter type 'distinctText': scans all image files on $pageId
     * and returns [{value: val, text: val}, ...] for each distinct non-empty value.
     *
     * Supports filter type 'tags': scans all image files on $pageId and
     * returns [{value: tag, text: tag}, ...] for each distinct tag, sorted alphabetically.
     *
     * @param array<string, array<string, mixed>> $filterDefs Filter definitions keyed by field name.
     * @param string                              $pageId     Kirby page ID of the parent page.
     * @return array<string, array<int, array{value: string, text: string}>>
     */
    public static function getOptions(array $filterDefs, string $pageId): array
    {
        $options    = [];
        $parentPage = kirby()->page($pageId);

        foreach ($filterDefs as $field => $def) {
            $type = $def['type'] ?? 'pages';

            if ($type === 'pages' && isset($def['collection'])) {
                $collection = kirby()->collection((string)$def['collection']);
                if ($collection !== null) {
                    $items = [];
                    foreach ($collection as $page) {
                        $items[] = ['value' => $page->id(), 'text' => $page->title()->value()];
                    }
                    $options[$field] = $items;
                }
            }

            if (($type === 'distinctText' || $type === 'tags') && $parentPage !== null) {
                $seen  = [];
                $items = [];
                foreach ($parentPage->files()->filterBy('template', 'image') as $file) {
                    if ($type === 'distinctText') {
                        $val = trim($file->content()->get($field)->value() ?? '');
                        if ($val !== '' && !in_array($val, $seen, true)) {
                            $seen[]  = $val;
                            $items[] = ['value' => $val, 'text' => $val];
                        }
                    } else {
                        $raw  = $file->content()->get($field)->value() ?? '';
                        $tags = $raw !== '' ? array_map('trim', explode(',', $raw)) : [];
                        foreach ($tags as $tag) {
                            if ($tag !== '' && !in_array($tag, $seen, true)) {
                                $seen[]  = $tag;
                                $items[] = ['value' => $tag, 'text' => $tag];
                            }
                        }
                    }
                }
                usort($items, fn(array $a, array $b): int => strcmp($a['text'], $b['text']));
                $options[$field] = $items;
            }
        }

        return $options;
    }

    /**
     * Returns a paginated, filtered, and sorted list of files on the given page.
     *
     * Loads all image-template files belonging to $pageId, converts them to
     * plain data arrays, then applies filters, search, sort, and pagination.
     *
     * @param string                              $pageId     Kirby page ID of the parent page.
     * @param array<string, array<string, mixed>> $filterDefs Filter definitions keyed by field name.
     * @param array<int, array<string, mixed>>    $columnDefs Column definitions (each has 'field', 'label', 'width').
     * @param array<string, string>               $active     Active filter values keyed by field name.
     * @param string                              $search     Freetext search string.
     * @param string                              $sortField  Field name to sort by.
     * @param string                              $sortDir    'asc' or 'desc'.
     * @param int                                 $page       1-based page number.
     * @param int                                 $pageSize   Items per page.
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, pageSize: int, totalPages: int}
     */
    public static function getResults(
        string $pageId,
        array $filterDefs,
        array $columnDefs,
        array $active,
        string $search,
        string $sortField,
        string $sortDir,
        int $page,
        int $pageSize
    ): array {
        $empty = ['items' => [], 'total' => 0, 'page' => 1, 'pageSize' => $pageSize, 'totalPages' => 0];

        $parentPage = kirby()->page($pageId);
        if ($parentPage === null) {
            KirbyBaseHelper::writeToLogFile('errors', 'FilteredFilesHelper: parent page not found: ' . $pageId);
            return $empty;
        }

        $files = [];
        foreach ($parentPage->files()->filterBy('template', 'image') as $file) {
            $files[] = self::kirbyFileToData($file, $filterDefs, $columnDefs);
        }

        $files = self::applyFilters($files, $filterDefs, $active);
        $files = self::applySearch($files, $search);
        $files = self::applySort($files, $sortField, $sortDir);

        return self::paginate($files, $page, $pageSize);
    }

    /**
     * Converts a single Kirby File into a plain data array.
     *
     * filterValues: resolves each filter field to the appropriate value format.
     * displayValues: collects field values needed for column display and sorting.
     * title: prefers the imageTitle field, falls back to the filename.
     * thumbUrl: a small cropped thumbnail URL for panel display.
     *
     * @param File                                $file       Kirby file object.
     * @param array<string, array<string, mixed>> $filterDefs Filter definitions keyed by field name.
     * @param array<int, array<string, mixed>>    $columnDefs Column definitions.
     * @return array<string, mixed>
     */
    private static function kirbyFileToData(File $file, array $filterDefs, array $columnDefs): array
    {
        $filterValues = [];
        foreach ($filterDefs as $fieldName => $def) {
            $type = $def['type'] ?? 'pages';
            if ($type === 'pages') {
                try {
                    $raw                      = $file->content()->get($fieldName)->value() ?? '';
                    $filterValues[$fieldName] = $raw !== '' ? Yaml::decode($raw) : [];
                } catch (InvalidArgumentException) {
                    $filterValues[$fieldName] = [];
                }
            } elseif ($type === 'distinctText') {
                try {
                    $filterValues[$fieldName] = trim($file->content()->get($fieldName)->value() ?? '');
                } catch (InvalidArgumentException) {
                    $filterValues[$fieldName] = '';
                }
            } elseif ($type === 'tags') {
                try {
                    $raw                      = $file->content()->get($fieldName)->value() ?? '';
                    $filterValues[$fieldName] = $raw !== '' ? array_map('trim', explode(',', $raw)) : [];
                } catch (InvalidArgumentException) {
                    $filterValues[$fieldName] = [];
                }
            }
        }

        try {
            $imageTitle = trim($file->content()->get('imageTitle')->value() ?? '');
        } catch (InvalidArgumentException) {
            $imageTitle = '';
        }
        $title      = $imageTitle !== '' ? $imageTitle : $file->filename();

        $displayValues = ['title' => $title, 'filename' => $file->filename()];
        foreach ($columnDefs as $col) {
            $colField = $col['field'] ?? '';
            if ($colField !== '' && !isset($displayValues[$colField])) {
                try {
                    $displayValues[$colField] = $file->content()->get($colField)->value() ?? '';
                } catch (InvalidArgumentException) {
                    $displayValues[$colField] = '';
                }
            }
        }

        $thumbUrl = $file->url();

        return [
            'id'            => $file->id(),
            'title'         => $title,
            'filename'      => $file->filename(),
            'panelUrl'      => $file->panel()->url(),
            'thumbUrl'      => $thumbUrl,
            'filterValues'  => $filterValues,
            'displayValues' => $displayValues,
        ];
    }
}
