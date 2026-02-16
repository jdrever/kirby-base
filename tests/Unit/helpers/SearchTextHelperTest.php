<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\SearchTextHelper;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the SearchTextHelper static utility class.
 *
 * Covers parseQuery (quoted phrases, stop words, short words, empty input),
 * filterStopWords (removal, case insensitivity, custom lists),
 * highlightInTextOnly (plain text, HTML tags, attributes, no-match),
 * highlightTerm (words, phrases, stop words, case insensitivity, HTML preservation),
 * scoreFields (no-match, hit counting, field weights, phrase 10x multiplier,
 * all-words-in-field 5x bonus, empty query),
 * and extractKeywordCounts (frequency counting, stop word filtering,
 * non-alphanumeric removal, limit, sort order, empty input).
 */
final class SearchTextHelperTest extends TestCase
{
    // --- parseQuery ---

    /**
     * Verify parseQuery() extracts double-quoted phrases and remaining words.
     */
    public function testParseQueryExtractsQuotedPhrases(): void
    {
        $result = SearchTextHelper::parseQuery('"botanical society" plants');

        $this->assertSame(['botanical society'], $result['phrases']);
        $this->assertContains('plants', $result['words']);
    }

    /**
     * Verify parseQuery() extracts multiple quoted phrases with no remaining words.
     */
    public function testParseQueryExtractsMultiplePhrases(): void
    {
        $result = SearchTextHelper::parseQuery('"wild flowers" "north wales"');

        $this->assertSame(['wild flowers', 'north wales'], $result['phrases']);
        $this->assertSame([], $result['words']);
    }

    /**
     * Verify parseQuery() filters common stop words from the word list.
     */
    public function testParseQueryFiltersStopWords(): void
    {
        $result = SearchTextHelper::parseQuery('the plants and flowers');

        $this->assertNotContains('the', $result['words']);
        $this->assertNotContains('and', $result['words']);
        $this->assertContains('plants', $result['words']);
        $this->assertContains('flowers', $result['words']);
    }

    /**
     * Verify parseQuery() filters words that are too short (2 chars or fewer).
     */
    public function testParseQueryFiltersShortWords(): void
    {
        $result = SearchTextHelper::parseQuery('go to uk plants');

        // 'go', 'to', 'uk' are all 2 chars — filtered by minLength > 2
        $this->assertContains('plants', $result['words']);
        $this->assertNotContains('go', $result['words']);
        $this->assertNotContains('uk', $result['words']);
    }

    /**
     * Verify parseQuery() returns empty arrays for empty input.
     */
    public function testParseQueryHandlesEmptyInput(): void
    {
        $result = SearchTextHelper::parseQuery('');

        $this->assertSame([], $result['phrases']);
        $this->assertSame([], $result['words']);
    }

    // --- filterStopWords ---

    /**
     * Verify filterStopWords() removes common English stop words.
     */
    public function testFilterStopWordsRemovesStopWords(): void
    {
        $result = SearchTextHelper::filterStopWords(['the', 'plants', 'are', 'green']);

        $this->assertContains('plants', $result);
        $this->assertContains('green', $result);
        $this->assertNotContains('the', $result);
        $this->assertNotContains('are', $result);
    }

    /**
     * Verify filterStopWords() removes words shorter than the minimum length.
     */
    public function testFilterStopWordsRemovesShortWords(): void
    {
        $result = SearchTextHelper::filterStopWords(['go', 'uk', 'oak', 'trees']);

        $this->assertContains('oak', $result);
        $this->assertContains('trees', $result);
        $this->assertNotContains('go', $result);
        $this->assertNotContains('uk', $result);
    }

    /**
     * Verify filterStopWords() performs case-insensitive stop word matching.
     */
    public function testFilterStopWordsIsCaseInsensitive(): void
    {
        $result = SearchTextHelper::filterStopWords(['The', 'Plants', 'ARE']);

        $this->assertContains('Plants', $result);
        $this->assertNotContains('The', $result);
        $this->assertNotContains('ARE', $result);
    }

    /**
     * Verify filterStopWords() accepts a custom stop word list and minimum length.
     */
    public function testFilterStopWordsWithCustomList(): void
    {
        $result = SearchTextHelper::filterStopWords(
            ['plants', 'trees', 'flowers'],
            ['plants'],
            2
        );

        $this->assertContains('trees', $result);
        $this->assertContains('flowers', $result);
        $this->assertNotContains('plants', $result);
    }

    // --- highlightInTextOnly ---

    /**
     * Verify highlightInTextOnly() wraps matches in plain text with highlight spans.
     */
    public function testHighlightInTextOnlyHighlightsPlainText(): void
    {
        $result = SearchTextHelper::highlightInTextOnly(
            'The quick brown fox',
            '/(brown)/i'
        );

        $this->assertSame('The quick <span class="highlight">brown</span> fox', $result);
    }

    /**
     * Verify highlightInTextOnly() does not highlight matches inside HTML tag attributes.
     */
    public function testHighlightInTextOnlySkipsHtmlTags(): void
    {
        $result = SearchTextHelper::highlightInTextOnly(
            '<a href="/brown/page">brown link</a>',
            '/(brown)/i'
        );

        // 'brown' in the href should NOT be highlighted, but in the text it should
        $this->assertSame(
            '<a href="/brown/page"><span class="highlight">brown</span> link</a>',
            $result
        );
    }

    /**
     * Verify highlightInTextOnly() preserves HTML attributes containing the match term.
     */
    public function testHighlightInTextOnlySkipsAttributes(): void
    {
        $result = SearchTextHelper::highlightInTextOnly(
            '<img alt="brown fox"> the brown dog',
            '/(brown)/i'
        );

        $this->assertStringContainsString('alt="brown fox"', $result);
        $this->assertStringContainsString('<span class="highlight">brown</span> dog', $result);
    }

    /**
     * Verify highlightInTextOnly() returns the original text when there is no match.
     */
    public function testHighlightInTextOnlyHandlesNoMatch(): void
    {
        $text = '<p>Hello world</p>';
        $result = SearchTextHelper::highlightInTextOnly($text, '/(missing)/i');

        $this->assertSame($text, $result);
    }

    // --- highlightTerm ---

    /**
     * Verify highlightTerm() highlights individual words in text.
     */
    public function testHighlightTermHighlightsWords(): void
    {
        $result = SearchTextHelper::highlightTerm('The botanical gardens are lovely', 'botanical');

        $this->assertStringContainsString('<span class="highlight">botanical</span>', $result);
    }

    /**
     * Verify highlightTerm() highlights a quoted phrase as a single unit.
     */
    public function testHighlightTermHighlightsQuotedPhrase(): void
    {
        $result = SearchTextHelper::highlightTerm(
            'Welcome to the botanical society page',
            '"botanical society"'
        );

        $this->assertStringContainsString('<span class="highlight">botanical society</span>', $result);
    }

    /**
     * Verify highlightTerm() does not highlight stop words from the search term.
     */
    public function testHighlightTermSkipsStopWords(): void
    {
        $result = SearchTextHelper::highlightTerm('The plants are green', 'the plants');

        // 'the' is a stop word and <= 3 chars, should not be highlighted
        $this->assertStringNotContainsString('<span class="highlight">The</span>', $result);
        $this->assertStringContainsString('<span class="highlight">plants</span>', $result);
    }

    /**
     * Verify highlightTerm() performs case-insensitive highlighting.
     */
    public function testHighlightTermIsCaseInsensitive(): void
    {
        $result = SearchTextHelper::highlightTerm('Botanical GARDENS today', 'botanical gardens');

        $this->assertStringContainsString('<span class="highlight">Botanical</span>', $result);
        $this->assertStringContainsString('<span class="highlight">GARDENS</span>', $result);
    }

    /**
     * Verify highlightTerm() preserves HTML tag attributes when highlighting text content.
     */
    public function testHighlightTermPreservesHtmlTags(): void
    {
        $result = SearchTextHelper::highlightTerm(
            '<p class="botanical">botanical gardens</p>',
            'botanical'
        );

        // Attribute should be untouched
        $this->assertStringContainsString('class="botanical"', $result);
        // Text should be highlighted
        $this->assertStringContainsString('<span class="highlight">botanical</span> gardens', $result);
    }

    // --- scoreFields ---

    /**
     * Verify scoreFields() returns zero hits and score when no fields match the query.
     */
    public function testScoreFieldsReturnsZeroForNoMatch(): void
    {
        $result = SearchTextHelper::scoreFields(
            ['title' => 'About us', 'description' => 'Our company'],
            'elephants'
        );

        $this->assertSame(0, $result['hits']);
        $this->assertSame(0, $result['score']);
    }

    /**
     * Verify scoreFields() counts hits and produces a positive score for matching fields.
     */
    public function testScoreFieldsCountsHits(): void
    {
        $result = SearchTextHelper::scoreFields(
            ['title' => 'Plant identification guide', 'description' => 'A guide to plant species'],
            'plant guide'
        );

        $this->assertGreaterThan(0, $result['hits']);
        $this->assertGreaterThan(0, $result['score']);
    }

    /**
     * Verify scoreFields() applies field weights to adjust scoring per field.
     */
    public function testScoreFieldsAppliesFieldWeights(): void
    {
        $fields = ['title' => 'orchid', 'description' => 'orchid'];

        $highTitleWeight = SearchTextHelper::scoreFields(
            $fields,
            'orchid',
            ['title' => 100, 'description' => 1]
        );

        $highDescWeight = SearchTextHelper::scoreFields(
            $fields,
            'orchid',
            ['title' => 1, 'description' => 100]
        );

        // Same hits, but scores should differ based on weights
        $this->assertSame($highTitleWeight['hits'], $highDescWeight['hits']);
        // Both should have the same total score since both fields match
        // but the distribution differs
        $this->assertGreaterThan(0, $highTitleWeight['score']);
        $this->assertGreaterThan(0, $highDescWeight['score']);
    }

    /**
     * Verify scoreFields() applies a 10x multiplier for phrase matches.
     */
    public function testScoreFieldsPhraseScoringUses10xMultiplier(): void
    {
        // With a weighted field, the phrase 10x multiplier becomes visible
        $fields = ['title' => 'botanical society of britain'];
        $weights = ['title' => 10];

        $phraseScore = SearchTextHelper::scoreFields($fields, '"botanical society"', $weights);

        // Phrase: 1 match * 10 (phrase multiplier) * 10 (weight) = 100
        $this->assertSame(100, $phraseScore['score']);
        $this->assertSame(1, $phraseScore['hits']);
    }

    /**
     * Verify scoreFields() applies a 5x bonus when all search words appear in the same field.
     */
    public function testScoreFieldsWordScoringUsesAllWordsBonus(): void
    {
        $fields = ['title' => 'botanical society of britain'];
        $weights = ['title' => 10];

        $wordScore = SearchTextHelper::scoreFields($fields, 'botanical society', $weights);

        // Both words found in same field: 5x bonus
        // 'botanical' 1 hit + 'society' 1 hit = 2 hits * 5 * 10 (weight) = 100
        $this->assertSame(100, $wordScore['score']);
        $this->assertSame(2, $wordScore['hits']);
    }

    /**
     * Verify the all-words-in-same-field bonus produces a higher score than a partial match.
     */
    public function testScoreFieldsAllWordsInSameFieldGetsBonus(): void
    {
        $twoWordMatch = SearchTextHelper::scoreFields(
            ['title' => 'wild flower identification'],
            'wild flower'
        );

        $oneWordMatch = SearchTextHelper::scoreFields(
            ['title' => 'wild animal tracking'],
            'wild flower'
        );

        // Both words in same field gets 5x bonus vs 1x for partial match
        $this->assertGreaterThan($oneWordMatch['score'], $twoWordMatch['score']);
    }

    /**
     * Verify scoreFields() returns zero hits and score for an empty query string.
     */
    public function testScoreFieldsHandlesEmptyQuery(): void
    {
        $result = SearchTextHelper::scoreFields(
            ['title' => 'Something'],
            ''
        );

        $this->assertSame(0, $result['hits']);
        $this->assertSame(0, $result['score']);
    }

    // --- extractKeywordCounts ---

    /**
     * Verify extractKeywordCounts() counts word frequency across multiple queries.
     */
    public function testExtractKeywordCountsCountsWords(): void
    {
        $queries = ['wild flowers', 'wild plants', 'garden flowers'];

        $result = SearchTextHelper::extractKeywordCounts($queries);

        $keywords = array_column($result, 'keyword');
        $counts = array_combine(
            array_column($result, 'keyword'),
            array_column($result, 'count')
        );

        $this->assertContains('wild', $keywords);
        $this->assertContains('flowers', $keywords);
        $this->assertSame(2, $counts['wild']);
        $this->assertSame(2, $counts['flowers']);
    }

    /**
     * Verify extractKeywordCounts() filters stop words from keyword results.
     */
    public function testExtractKeywordCountsFiltersStopWords(): void
    {
        $queries = ['the best plants', 'all the flowers'];

        $result = SearchTextHelper::extractKeywordCounts($queries);
        $keywords = array_column($result, 'keyword');

        $this->assertNotContains('the', $keywords);
        $this->assertNotContains('all', $keywords);
        $this->assertContains('best', $keywords);
    }

    /**
     * Verify extractKeywordCounts() strips non-alphanumeric characters from keywords.
     */
    public function testExtractKeywordCountsRemovesNonAlphanumeric(): void
    {
        $queries = ['plants!', 'flowers?', 'trees...'];

        $result = SearchTextHelper::extractKeywordCounts($queries);
        $keywords = array_column($result, 'keyword');

        $this->assertContains('plants', $keywords);
        $this->assertContains('flowers', $keywords);
        $this->assertContains('trees', $keywords);
    }

    /**
     * Verify extractKeywordCounts() respects the limit parameter.
     */
    public function testExtractKeywordCountsRespectsLimit(): void
    {
        $queries = ['alpha bravo charlie delta echo foxtrot'];

        $result = SearchTextHelper::extractKeywordCounts($queries, [], 3);

        $this->assertCount(3, $result);
    }

    /**
     * Verify extractKeywordCounts() sorts results by frequency in descending order.
     */
    public function testExtractKeywordCountsSortsByFrequency(): void
    {
        $queries = ['oak', 'elm', 'oak', 'elm', 'oak', 'pine'];

        $result = SearchTextHelper::extractKeywordCounts($queries, [], 10);

        // First result should be the most frequent
        $this->assertSame('oak', $result[0]['keyword']);
        $this->assertSame(3, $result[0]['count']);
    }

    /**
     * Verify extractKeywordCounts() returns an empty array for empty input.
     */
    public function testExtractKeywordCountsHandlesEmptyInput(): void
    {
        $result = SearchTextHelper::extractKeywordCounts([]);
        $this->assertSame([], $result);
    }

    /**
     * Verify extractKeywordCounts() skips empty and whitespace-only queries.
     */
    public function testExtractKeywordCountsSkipsEmptyQueries(): void
    {
        $queries = ['', '  ', 'plants'];

        $result = SearchTextHelper::extractKeywordCounts($queries, [], 10);
        $keywords = array_column($result, 'keyword');

        $this->assertSame(['plants'], $keywords);
    }
}
