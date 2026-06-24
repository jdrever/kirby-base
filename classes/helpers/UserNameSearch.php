<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Pure, Kirby-independent matching logic for narrowing a user picker by full name.
 *
 * Kirby's default search component matches *any* of the typed words (OR) across a
 * user's name, email and role, so searching "Janet Simkin" against a large user
 * base returns everyone called Janet or anyone called Simkin. This helper instead
 * requires *every* typed word to appear in the user's name (AND), dramatically
 * narrowing the result set.
 *
 * Used by {@see \BSBI\WebBase\cms\UserNamePicker}.
 */
final class UserNameSearch
{
    /**
     * Minimum length of a search token. Shorter tokens are treated as noise and
     * dropped, matching the default `minlength` of Kirby's search component.
     */
    public const int MIN_LENGTH = 2;

    /**
     * Splits a raw search query into lowercased search words.
     *
     * Tokens shorter than {@see self::MIN_LENGTH} characters are dropped.
     *
     * @param string $query the raw query typed into the picker
     * @param int $minLength minimum token length to keep
     * @return list<string> lowercased search words (possibly empty)
     */
    public static function words(string $query, int $minLength = self::MIN_LENGTH): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', mb_strtolower($query)) ?: [];

        return array_values(array_filter(
            $parts,
            static fn (string $word): bool => mb_strlen($word) >= $minLength
        ));
    }

    /**
     * Determines whether a user's name contains every given search word.
     *
     * Matching is case-insensitive and substring-based, so "sim" matches "Simkin".
     * An empty word list is treated as a non-match — callers should short-circuit
     * before calling this when there is nothing to search for.
     *
     * @param string $name the user's full name
     * @param list<string> $words lowercased search words from {@see self::words()}
     * @return bool true only if every word appears in the name
     */
    public static function nameMatches(string $name, array $words): bool
    {
        if ($words === []) {
            return false;
        }

        $name = mb_strtolower($name);

        foreach ($words as $word) {
            if (str_contains($name, $word) === false) {
                return false;
            }
        }

        return true;
    }
}
