<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Pure text-processing utilities for search: highlighting, stop word filtering,
 * query parsing, keyword extraction, and scoring.
 *
 * All methods are static and have no Kirby dependencies, making them easy to test.
 */
class SearchTextHelper
{
    public const array STOP_WORDS = [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
        'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need',
        'this', 'that', 'these', 'those', 'it', 'its'
    ];

    public const array SEARCH_STOP_WORDS = [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
        'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need',
        'this', 'that', 'these', 'those', 'it', 'its', 'i', 'me', 'my', 'we',
        'our', 'you', 'your', 'he', 'she', 'they', 'them', 'their', 'what',
        'which', 'who', 'whom', 'how', 'when', 'where', 'why', 'all', 'any',
        'both', 'each', 'more', 'most', 'other', 'some', 'such', 'no', 'not',
        'only', 'same', 'so', 'than', 'too', 'very', 'just', 'also', 'now'
    ];

    /**
     * Parse a search query into quoted phrases and individual words (stop words removed).
     *
     * @param string $query The raw search query
     * @param string[] $stopWords Stop word list to filter against
     * @return array{phrases: string[], words: string[]}
     */
    public static function parseQuery(string $query, array $stopWords = self::STOP_WORDS): array
    {
        // Extract quoted phrases
        preg_match_all('/"([^"]+)"/', $query, $phraseMatches);
        $phrases = $phraseMatches[1] ?? [];

        // Get remaining words after removing quoted phrases
        $remaining = preg_replace('/"[^"]+"/', '', $query);
        $words = preg_split('/\s+/', trim($remaining ?? ''));

        if ($words === false) {
            $words = [];
        }

        $words = self::filterStopWords(array_filter($words), $stopWords);

        return ['phrases' => $phrases, 'words' => $words];
    }

    /**
     * Filter stop words and short words from an array of search terms.
     *
     * @param string[] $words
     * @param string[] $stopWords
     * @param int $minLength Minimum word length to keep (exclusive)
     * @return string[]
     */
    public static function filterStopWords(
        array $words,
        array $stopWords = self::STOP_WORDS,
        int $minLength = 2
    ): array {
        return array_filter($words, fn(string $word) =>
            strlen($word) > $minLength && !in_array(strtolower($word), $stopWords, true)
        );
    }

    /**
     * Highlight search terms in HTML text, only modifying text content (not tags/attributes).
     *
     * @param string $text The HTML text to process
     * @param string $term The search term (may include quoted phrases)
     * @param string[] $stopWords Stop word list
     * @return string The text with <span class="highlight"> applied
     */
    public static function highlightTerm(
        string $text,
        string $term,
        array $stopWords = self::STOP_WORDS
    ): string {
        $parsed = self::parseQuery($term, $stopWords);

        // Highlight exact phrases first
        foreach ($parsed['phrases'] as $phrase) {
            $escaped = preg_quote($phrase, '/');
            $text = self::highlightInTextOnly($text, "/($escaped)/i");
        }

        // Highlight individual words (skip very short words and stop words)
        foreach ($parsed['words'] as $word) {
            if (strlen($word) > 2 && !in_array(strtolower($word), $stopWords, true)) {
                $escaped = preg_quote($word, '/');
                $text = self::highlightInTextOnly($text, "/(\b$escaped\b)/i");
            }
        }

        return $text;
    }

    /**
     * Apply a regex pattern as highlighting only to text content, skipping HTML tags.
     *
     * @param string $text The HTML text to process
     * @param string $pattern Regex with a capture group for the match
     * @return string
     */
    public static function highlightInTextOnly(string $text, string $pattern): string
    {
        $parts = preg_split('/(<[^>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $text;
        }

        $result = '';
        foreach ($parts as $part) {
            if (str_starts_with($part, '<')) {
                $result .= $part;
            } else {
                $result .= preg_replace($pattern, '<span class="highlight">$1</span>', $part) ?? $part;
            }
        }

        return $result;
    }

    /**
     * Score a set of fields against a search query.
     *
     * @param array<string, string> $fields Map of field name => field value
     * @param string $query The search query
     * @param array<string, int> $fieldWeights Map of field name => weight multiplier
     * @param string[] $stopWords Stop word list
     * @return array{hits: int, score: int}
     */
    public static function scoreFields(
        array $fields,
        string $query,
        array $fieldWeights = [],
        array $stopWords = self::STOP_WORDS
    ): array {
        $parsed = self::parseQuery($query, $stopWords);
        $scoring = ['hits' => 0, 'score' => 0];

        foreach ($fields as $fieldName => $value) {
            $weight = $fieldWeights[$fieldName] ?? 1;

            // Quoted phrase matches (highest priority - 10x)
            foreach ($parsed['phrases'] as $phrase) {
                $matches = preg_match_all('!' . preg_quote($phrase) . '!i', $value, $r);
                if ($matches) {
                    $scoring['score'] += 10 * $matches * $weight;
                    $scoring['hits'] += $matches;
                }
            }

            // Individual word matches
            $allWords = true;
            $wordMatches = 0;
            foreach ($parsed['words'] as $word) {
                $escapedWord = preg_quote($word, '/');
                $pattern = "/\b" . $escapedWord . "\b/i";

                $matches = preg_match_all($pattern, $value, $r);
                if ($matches) {
                    $wordMatches += $matches;
                } else {
                    $allWords = false;
                }
            }

            // Bonus if all words found in same field (5x vs 1x)
            if ($allWords && count($parsed['words']) > 0) {
                $scoring['score'] += 5 * $wordMatches * $weight;
            } else {
                $scoring['score'] += $wordMatches * $weight;
            }
            $scoring['hits'] += $wordMatches;
        }

        return $scoring;
    }

    /**
     * Extract keyword counts from an array of search queries.
     *
     * @param string[] $queries Raw search query strings
     * @param string[] $stopWords Stop word list
     * @param int $limit Maximum keywords to return
     * @return array<array{keyword: string, count: int}>
     */
    public static function extractKeywordCounts(
        array $queries,
        array $stopWords = self::SEARCH_STOP_WORDS,
        int $limit = 20
    ): array {
        $keywordCounts = [];

        foreach ($queries as $query) {
            $query = strtolower(trim($query));
            if ($query === '') {
                continue;
            }

            $words = preg_split('/\s+/', $query);
            foreach ($words as $word) {
                $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
                if (strlen($word) >= 2 && !in_array($word, $stopWords, true)) {
                    $keywordCounts[$word] = ($keywordCounts[$word] ?? 0) + 1;
                }
            }
        }

        arsort($keywordCounts);
        $topKeywords = array_slice($keywordCounts, 0, $limit, true);

        $result = [];
        foreach ($topKeywords as $keyword => $count) {
            $result[] = ['keyword' => $keyword, 'count' => $count];
        }

        return $result;
    }
}
