<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\FileLinkIndexHelper;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the FileLinkIndexHelper reverse-link index.
 *
 * Exercises the Kirby-independent core: token extraction, indexing page
 * content, querying linking pages, and incremental removal/re-indexing.
 * Uses a temporary on-disk SQLite file so no Kirby environment is required.
 */
final class FileLinkIndexHelperTest extends TestCase
{
    private string $dbPath;
    private FileLinkIndexHelper $index;

    protected function setUp(): void
    {
        $this->dbPath = tempnam(sys_get_temp_dir(), 'filelinks_') . '.sqlite';
        $this->index  = new FileLinkIndexHelper($this->dbPath);
    }

    protected function tearDown(): void
    {
        foreach ([$this->dbPath, $this->dbPath . '-wal', $this->dbPath . '-shm'] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // ── Token extraction ──────────────────────────────────────────────────

    public function testExtractFileUuidsFindsAllUuidTokens(): void
    {
        $text = 'See file://M7CPnFdVe6hgn5LU and also (file://oij4cvsxsjafgisk) here.';
        $this->assertSame(
            ['M7CPnFdVe6hgn5LU', 'oij4cvsxsjafgisk'],
            FileLinkIndexHelper::extractFileUuids($text)
        );
    }

    public function testExtractFileUuidsDeduplicates(): void
    {
        $text = 'file://abc123 then again file://abc123';
        $this->assertSame(['abc123'], FileLinkIndexHelper::extractFileUuids($text));
    }

    public function testExtractFileUuidsReturnsEmptyWhenNone(): void
    {
        $this->assertSame([], FileLinkIndexHelper::extractFileUuids('no links here'));
    }

    public function testExtractPermanentUrlSegmentsFindsSegments(): void
    {
        $text = 'Download https://bsbi.org/files/annual-report.pdf or /files/minutes-2024';
        $segments = FileLinkIndexHelper::extractPermanentUrlSegments($text);
        $this->assertContains('annual-report.pdf', $segments);
        $this->assertContains('minutes-2024', $segments);
    }

    // ── Template exclusion ─────────────────────────────────────────────────

    public function testWrapperTemplatesAreExcluded(): void
    {
        $this->assertTrue(FileLinkIndexHelper::isExcludedTemplate('page_link'));
        $this->assertTrue(FileLinkIndexHelper::isExcludedTemplate('file_archive'));
    }

    public function testFileLinkTemplateIsNotExcluded(): void
    {
        // file_link pages wrap a single file to give it a permanent URL; they are
        // the only direct reference to that file, so they MUST be indexed (see #535).
        $this->assertFalse(FileLinkIndexHelper::isExcludedTemplate('file_link'));
    }

    public function testContentTemplatesAreNotExcluded(): void
    {
        $this->assertFalse(FileLinkIndexHelper::isExcludedTemplate('default'));
        $this->assertFalse(FileLinkIndexHelper::isExcludedTemplate('blog_post'));
        $this->assertFalse(FileLinkIndexHelper::isExcludedTemplate('policy'));
    }

    // ── Indexing + querying ────────────────────────────────────────────────

    public function testIndexAndQueryUuidLink(): void
    {
        $this->index->indexPageContent('about/team', 'Bio file://M7CPnFdVe6hgn5LU here', []);

        $pages = $this->index->getLinkingPages('M7CPnFdVe6hgn5LU');
        $this->assertCount(1, $pages);
        $this->assertSame('about/team', $pages[0]['pageId']);
        $this->assertSame('uuid', $pages[0]['linkTypes']);
    }

    public function testQueryReturnsEmptyForUnknownUuid(): void
    {
        $this->index->indexPageContent('about/team', 'file://abc123', []);
        $this->assertSame([], $this->index->getLinkingPages('does-not-exist'));
    }

    public function testMultiplePagesLinkingSameFile(): void
    {
        $this->index->indexPageContent('about/team', 'file://abc123', []);
        $this->index->indexPageContent('news/story-1', 'see file://abc123', []);

        $pages = array_column($this->index->getLinkingPages('abc123'), 'pageId');
        $this->assertEqualsCanonicalizing(['about/team', 'news/story-1'], $pages);
    }

    public function testPermanentUrlLinkResolvedViaMap(): void
    {
        // Map: permanentUrl segment => file uuid
        $map = ['annual-report.pdf' => 'rep123uuid'];
        $this->index->indexPageContent(
            'about/reports',
            'Read our <a href="https://bsbi.org/files/annual-report.pdf">report</a>',
            $map
        );

        $pages = $this->index->getLinkingPages('rep123uuid');
        $this->assertCount(1, $pages);
        $this->assertSame('about/reports', $pages[0]['pageId']);
        $this->assertStringContainsString('permanent_url', $pages[0]['linkTypes']);
    }

    public function testPageLinkingViaBothFormsRecordsBothTypes(): void
    {
        $map = ['report.pdf' => 'abc123'];
        $this->index->indexPageContent(
            'about/reports',
            'file://abc123 and https://bsbi.org/files/report.pdf',
            $map
        );

        $pages = $this->index->getLinkingPages('abc123');
        $this->assertCount(1, $pages, 'Same page should appear once');
        $this->assertStringContainsString('uuid', $pages[0]['linkTypes']);
        $this->assertStringContainsString('permanent_url', $pages[0]['linkTypes']);
    }

    public function testFileLinkContentRecordsFileLinkType(): void
    {
        // A file_link wrapper page references its wrapped file via a file:// token
        // in the `file` field; the wrapped file must be recorded as used (#535).
        $this->index->indexFileLinkContent('downloads/annual-report', '- file://abc123');

        $pages = $this->index->getLinkingPages('abc123');
        $this->assertCount(1, $pages);
        $this->assertSame('downloads/annual-report', $pages[0]['pageId']);
        $this->assertSame('file_link', $pages[0]['linkTypes']);
    }

    public function testFileLinkContentIsIdempotent(): void
    {
        $this->index->indexFileLinkContent('downloads/report', 'file://oldfile');
        // Wrapper re-pointed at a different file
        $this->index->indexFileLinkContent('downloads/report', 'file://newfile');

        $this->assertSame([], $this->index->getLinkingPages('oldfile'));
        $this->assertCount(1, $this->index->getLinkingPages('newfile'));
    }

    public function testFileLinkContentWithNoTokensInsertsNothing(): void
    {
        // A malformed/empty file_link wrapper (no file:// token) records nothing
        // and clears any prior rows for the page.
        $this->index->indexFileLinkContent('downloads/empty', 'file://abc123');
        $this->assertCount(1, $this->index->getLinkingPages('abc123'));

        $this->assertSame(0, $this->index->indexFileLinkContent('downloads/empty', 'no tokens here'));
        $this->assertSame([], $this->index->getLinkingPages('abc123'));
    }

    public function testReindexingPageReplacesPreviousLinks(): void
    {
        $this->index->indexPageContent('about/team', 'file://oldfile', []);
        $this->assertCount(1, $this->index->getLinkingPages('oldfile'));

        // Page edited: now links a different file
        $this->index->indexPageContent('about/team', 'file://newfile', []);

        $this->assertSame([], $this->index->getLinkingPages('oldfile'));
        $this->assertCount(1, $this->index->getLinkingPages('newfile'));
    }

    public function testRemovePageClearsLinks(): void
    {
        $this->index->indexPageContent('about/team', 'file://abc123', []);
        $this->index->removePage('about/team');
        $this->assertSame([], $this->index->getLinkingPages('abc123'));
    }

    public function testStatsReportsCounts(): void
    {
        $this->index->indexPageContent('a', 'file://x', []);
        $this->index->indexPageContent('b', 'file://x file://y', []);

        $stats = $this->index->getStats();
        $this->assertSame(2, $stats['total_files']);
        $this->assertSame(2, $stats['total_pages']);
        $this->assertSame(3, $stats['total_links']);
    }
}
