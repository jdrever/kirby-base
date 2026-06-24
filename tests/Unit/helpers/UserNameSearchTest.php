<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\UserNameSearch;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pure UserNameSearch matching logic.
 *
 * These methods have no Kirby dependency — they drive the narrowed,
 * AND-of-words name matching used by {@see \BSBI\WebBase\cms\UserNamePicker}
 * to replace Kirby's default OR-of-words search across name/email/role.
 */
final class UserNameSearchTest extends TestCase
{
    // ── words() ───────────────────────────────────────────────────────────

    public function testWordsSplitsAndLowercasesQuery(): void
    {
        $this->assertSame(['janet', 'simkin'], UserNameSearch::words('Janet Simkin'));
    }

    public function testWordsCollapsesRepeatedWhitespace(): void
    {
        $this->assertSame(['janet', 'simkin'], UserNameSearch::words("  Janet   \t Simkin  "));
    }

    public function testWordsDropsTokensBelowMinLength(): void
    {
        // single-character tokens are noise and are dropped (matches Kirby's minlength)
        $this->assertSame(['janet'], UserNameSearch::words('Janet X'));
    }

    public function testWordsReturnsEmptyForBlankQuery(): void
    {
        $this->assertSame([], UserNameSearch::words(''));
        $this->assertSame([], UserNameSearch::words('   '));
    }

    // ── nameMatches() ─────────────────────────────────────────────────────

    public function testNameMatchesWhenAllWordsPresent(): void
    {
        $this->assertTrue(UserNameSearch::nameMatches('Dr Janet Simkin', ['janet', 'simkin']));
    }

    public function testNameMatchesIsCaseInsensitive(): void
    {
        $this->assertTrue(UserNameSearch::nameMatches('JANET SIMKIN', ['janet', 'simkin']));
    }

    public function testNameMatchesIgnoresWordOrder(): void
    {
        $this->assertTrue(UserNameSearch::nameMatches('Dr Janet Simkin', ['simkin', 'janet']));
    }

    public function testNameDoesNotMatchWhenOneWordMissing(): void
    {
        // the core bug: "Janet Smith" must NOT match a search for "Janet Simkin"
        $this->assertFalse(UserNameSearch::nameMatches('Janet Smith', ['janet', 'simkin']));
    }

    public function testNameMatchesPartialSurnameSearch(): void
    {
        $this->assertTrue(UserNameSearch::nameMatches('Dr Janet Simkin', ['simkin']));
    }

    public function testNameMatchesSubstringWithinWord(): void
    {
        $this->assertTrue(UserNameSearch::nameMatches('Dr Janet Simkin', ['sim']));
    }

    public function testEmptyWordListIsNotAMatch(): void
    {
        // caller (the picker) is expected to short-circuit on no words;
        // the matcher itself treats an empty word list as a non-match.
        $this->assertFalse(UserNameSearch::nameMatches('Dr Janet Simkin', []));
    }
}
