<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\SearchService;
use PHPUnit\Framework\TestCase;

/**
 * Tests for SearchService static utility methods.
 *
 * normaliseQueryPunctuation() is a public static method and can be tested
 * directly without a Kirby environment.
 */
final class SearchServiceTest extends TestCase
{
    // --- normaliseQueryPunctuation ---

    /**
     * Verify that hyphens are replaced with spaces so FTS5 does not treat
     * them as NOT operators (e.g. "red dead-nettle" → "red dead nettle").
     */
    public function testNormaliseQueryReplacesHyphensWithSpaces(): void
    {
        $this->assertSame('red dead nettle', SearchService::normaliseQueryPunctuation('red dead-nettle'));
    }

    /**
     * Verify that multiple hyphens in a compound name are all replaced.
     */
    public function testNormaliseQueryReplacesMultipleHyphens(): void
    {
        $this->assertSame('forget me not', SearchService::normaliseQueryPunctuation('forget-me-not'));
    }

    /**
     * Verify that parentheses are replaced with spaces.
     */
    public function testNormaliseQueryReplacesParentheses(): void
    {
        $this->assertSame('Linn.', SearchService::normaliseQueryPunctuation('(Linn.)'));
    }

    /**
     * Verify that commas are replaced with spaces.
     */
    public function testNormaliseQueryReplacesCommas(): void
    {
        $this->assertSame('sedges rushes', SearchService::normaliseQueryPunctuation('sedges, rushes'));
    }

    /**
     * Verify that slashes are replaced with spaces.
     */
    public function testNormaliseQueryReplacesSlashes(): void
    {
        $this->assertSame('moss liverwort', SearchService::normaliseQueryPunctuation('moss/liverwort'));
    }

    /**
     * Verify that square brackets are replaced with spaces.
     */
    public function testNormaliseQueryReplacesSquareBrackets(): void
    {
        $this->assertSame('Linn.', SearchService::normaliseQueryPunctuation('[Linn.]'));
    }

    /**
     * Verify that consecutive spaces produced by replacements are collapsed to one.
     */
    public function testNormaliseQueryCollapsesMultipleSpaces(): void
    {
        $this->assertSame('red dead nettle', SearchService::normaliseQueryPunctuation('red  dead--nettle'));
    }

    /**
     * Verify that leading and trailing whitespace is trimmed.
     */
    public function testNormaliseQueryTrimsWhitespace(): void
    {
        $this->assertSame('red dead nettle', SearchService::normaliseQueryPunctuation(' red dead-nettle '));
    }

    /**
     * Verify that plain words with no special punctuation are returned unchanged.
     */
    public function testNormaliseQueryLeavesPlainWordsUnchanged(): void
    {
        $this->assertSame('red kite', SearchService::normaliseQueryPunctuation('red kite'));
    }

    /**
     * Verify that an empty string is returned unchanged.
     */
    public function testNormaliseQueryHandlesEmptyString(): void
    {
        $this->assertSame('', SearchService::normaliseQueryPunctuation(''));
    }
}
