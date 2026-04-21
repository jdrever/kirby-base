<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\FilteredFilesHelper;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FilteredFilesHelper pure methods.
 *
 * Only the pure static methods (applyFilters, applySearch, applySort, paginate)
 * are tested here — they have no Kirby dependency. The Kirby-dependent methods
 * (getOptions, getResults) require a running Kirby instance and are covered by
 * integration tests at the site level.
 */
final class FilteredFilesHelperTest extends TestCase
{
    // ── Fixtures ──────────────────────────────────────────────────────────

    /**
     * Returns a minimal set of file data arrays covering the main filter types.
     *
     * @return array<int, array<string, mixed>>
     */
    private function makeFiles(): array
    {
        return [
            [
                'id'       => 'image-bank/bluebell.jpg',
                'title'    => 'Bluebell Wood',
                'filename' => 'bluebell.jpg',
                'panelUrl' => '/panel/pages/image-bank/files/bluebell.jpg',
                'thumbUrl' => '',
                'filterValues' => [
                    'photographer' => 'Jane Smith',
                    'tags'         => ['Woodland', 'Flowers'],
                    'taxa'         => ['taxa/hyacinthoides-non-scripta'],
                ],
                'displayValues' => [
                    'title'        => 'Bluebell Wood',
                    'filename'     => 'bluebell.jpg',
                    'photographer' => 'Jane Smith',
                    'tags'         => 'Woodland,Flowers',
                ],
            ],
            [
                'id'       => 'image-bank/oak-tree.jpg',
                'title'    => 'Oak Tree',
                'filename' => 'oak-tree.jpg',
                'panelUrl' => '/panel/pages/image-bank/files/oak-tree.jpg',
                'thumbUrl' => '',
                'filterValues' => [
                    'photographer' => 'John Brown',
                    'tags'         => ['Woodland', 'Trees'],
                    'taxa'         => ['taxa/quercus-robur'],
                ],
                'displayValues' => [
                    'title'        => 'Oak Tree',
                    'filename'     => 'oak-tree.jpg',
                    'photographer' => 'John Brown',
                    'tags'         => 'Woodland,Trees',
                ],
            ],
            [
                'id'       => 'image-bank/puffin.jpg',
                'title'    => 'Puffin',
                'filename' => 'puffin.jpg',
                'panelUrl' => '/panel/pages/image-bank/files/puffin.jpg',
                'thumbUrl' => '',
                'filterValues' => [
                    'photographer' => 'Jane Smith',
                    'tags'         => ['Coastal', 'Birds'],
                    'taxa'         => ['taxa/fratercula-arctica'],
                ],
                'displayValues' => [
                    'title'        => 'Puffin',
                    'filename'     => 'puffin.jpg',
                    'photographer' => 'Jane Smith',
                    'tags'         => 'Coastal,Birds',
                ],
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function makeFilterDefs(): array
    {
        return [
            'photographer' => ['type' => 'distinctText'],
            'tags'         => ['type' => 'tags'],
            'taxa'         => ['type' => 'pages', 'collection' => 'uncachedTaxa'],
        ];
    }

    // ── applyFilters — distinctText ────────────────────────────────────────

    public function testApplyFilters_emptyActive_returnsAllFiles(): void
    {
        $result = FilteredFilesHelper::applyFilters($this->makeFiles(), $this->makeFilterDefs(), []);
        $this->assertCount(3, $result);
    }

    public function testApplyFilters_distinctText_matchingPhotographer(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['photographer' => 'Jane Smith']
        );
        $this->assertCount(2, $result);
        $titles = array_column($result, 'title');
        $this->assertContains('Bluebell Wood', $titles);
        $this->assertContains('Puffin', $titles);
    }

    public function testApplyFilters_distinctText_noMatch_returnsEmpty(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['photographer' => 'Unknown Photographer']
        );
        $this->assertCount(0, $result);
    }

    public function testApplyFilters_distinctText_emptyValue_isIgnored(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['photographer' => '']
        );
        $this->assertCount(3, $result);
    }

    // ── applyFilters — tags ────────────────────────────────────────────────

    public function testApplyFilters_tags_matchingSingleTag(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['tags' => 'Woodland']
        );
        $this->assertCount(2, $result);
        $titles = array_column($result, 'title');
        $this->assertContains('Bluebell Wood', $titles);
        $this->assertContains('Oak Tree', $titles);
    }

    public function testApplyFilters_tags_noMatch_returnsEmpty(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['tags' => 'Mountains']
        );
        $this->assertCount(0, $result);
    }

    public function testApplyFilters_tags_emptyValue_isIgnored(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['tags' => '']
        );
        $this->assertCount(3, $result);
    }

    // ── applyFilters — pages ───────────────────────────────────────────────

    public function testApplyFilters_pages_matchingTaxon(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['taxa' => 'taxa/quercus-robur']
        );
        $this->assertCount(1, $result);
        $this->assertSame('Oak Tree', $result[0]['title']);
    }

    public function testApplyFilters_pages_noMatch_returnsEmpty(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['taxa' => 'taxa/nonexistent']
        );
        $this->assertCount(0, $result);
    }

    // ── applyFilters — combined ────────────────────────────────────────────

    public function testApplyFilters_combined_andLogic(): void
    {
        // Jane Smith + Woodland → only Bluebell Wood (Puffin is Jane Smith but not Woodland)
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['photographer' => 'Jane Smith', 'tags' => 'Woodland']
        );
        $this->assertCount(1, $result);
        $this->assertSame('Bluebell Wood', $result[0]['title']);
    }

    public function testApplyFilters_unknownFilterField_isIgnored(): void
    {
        $result = FilteredFilesHelper::applyFilters(
            $this->makeFiles(),
            $this->makeFilterDefs(),
            ['nonexistent' => 'some-value']
        );
        $this->assertCount(3, $result);
    }

    // ── applySearch ───────────────────────────────────────────────────────

    public function testApplySearch_emptyString_returnsAllFiles(): void
    {
        $result = FilteredFilesHelper::applySearch($this->makeFiles(), '');
        $this->assertCount(3, $result);
    }

    public function testApplySearch_matchingSubstring_returnsMatch(): void
    {
        $result = FilteredFilesHelper::applySearch($this->makeFiles(), 'puffin');
        $this->assertCount(1, $result);
        $this->assertSame('Puffin', $result[0]['title']);
    }

    public function testApplySearch_caseInsensitive(): void
    {
        $result = FilteredFilesHelper::applySearch($this->makeFiles(), 'OAK');
        $this->assertCount(1, $result);
        $this->assertSame('Oak Tree', $result[0]['title']);
    }

    public function testApplySearch_noMatch_returnsEmpty(): void
    {
        $result = FilteredFilesHelper::applySearch($this->makeFiles(), 'zzznomatch');
        $this->assertCount(0, $result);
    }

    public function testApplySearch_partialMatch_returnsMultiple(): void
    {
        // 'oo' appears in 'Bluebell Wood' but not 'Oak Tree' or 'Puffin'
        $result = FilteredFilesHelper::applySearch($this->makeFiles(), 'oo');
        $this->assertCount(1, $result);
        $this->assertSame('Bluebell Wood', $result[0]['title']);
    }

    // ── applySort ─────────────────────────────────────────────────────────

    public function testApplySort_byFilenameAsc_returnsAlphabeticalOrder(): void
    {
        $result = FilteredFilesHelper::applySort($this->makeFiles(), 'filename', 'asc');
        $this->assertSame('bluebell.jpg', $result[0]['filename']);
        $this->assertSame('oak-tree.jpg', $result[1]['filename']);
        $this->assertSame('puffin.jpg',   $result[2]['filename']);
    }

    public function testApplySort_byFilenameDesc_returnsReverseOrder(): void
    {
        $result = FilteredFilesHelper::applySort($this->makeFiles(), 'filename', 'desc');
        $this->assertSame('puffin.jpg',   $result[0]['filename']);
        $this->assertSame('oak-tree.jpg', $result[1]['filename']);
        $this->assertSame('bluebell.jpg', $result[2]['filename']);
    }

    public function testApplySort_byPhotographerAsc(): void
    {
        $result = FilteredFilesHelper::applySort($this->makeFiles(), 'photographer', 'asc');
        // 'Jane Smith' < 'John Brown' lexicographically
        $this->assertSame('Jane Smith', $result[0]['displayValues']['photographer']);
        $this->assertSame('Jane Smith', $result[1]['displayValues']['photographer']);
        $this->assertSame('John Brown', $result[2]['displayValues']['photographer']);
    }

    // ── paginate ──────────────────────────────────────────────────────────

    public function testPaginate_firstPage_returnsCorrectSlice(): void
    {
        $result = FilteredFilesHelper::paginate($this->makeFiles(), 1, 2);
        $this->assertSame(3, $result['total']);
        $this->assertSame(2, $result['totalPages']);
        $this->assertSame(1, $result['page']);
        $this->assertCount(2, $result['items']);
    }

    public function testPaginate_secondPage_returnsRemainder(): void
    {
        $result = FilteredFilesHelper::paginate($this->makeFiles(), 2, 2);
        $this->assertCount(1, $result['items']);
        $this->assertSame('puffin.jpg', $result['items'][0]['filename']);
    }

    public function testPaginate_pageSizeLargerThanTotal_returnsSinglePage(): void
    {
        $result = FilteredFilesHelper::paginate($this->makeFiles(), 1, 50);
        $this->assertSame(1, $result['totalPages']);
        $this->assertCount(3, $result['items']);
    }

    public function testPaginate_emptyInput_returnsZeroTotals(): void
    {
        $result = FilteredFilesHelper::paginate([], 1, 25);
        $this->assertSame(0, $result['total']);
        $this->assertSame(0, $result['totalPages']);
        $this->assertCount(0, $result['items']);
    }

    public function testPaginate_totalPagesRoundsUp(): void
    {
        $files  = array_fill(0, 5, $this->makeFiles()[0]);
        $result = FilteredFilesHelper::paginate($files, 1, 2);
        $this->assertSame(3, $result['totalPages']);
    }

    // ── combined pipeline ─────────────────────────────────────────────────

    public function testCombinedPipeline_filterSearchSortPaginate(): void
    {
        $files = $this->makeFiles();

        // Filter: Jane Smith (Bluebell Wood + Puffin)
        $files = FilteredFilesHelper::applyFilters($files, $this->makeFilterDefs(), ['photographer' => 'Jane Smith']);
        // Search: 'bluebell' → Bluebell Wood only
        $files = FilteredFilesHelper::applySearch($files, 'bluebell');
        $files = FilteredFilesHelper::applySort($files, 'filename', 'asc');
        $result = FilteredFilesHelper::paginate($files, 1, 25);

        $this->assertSame(1, $result['total']);
        $this->assertSame('Bluebell Wood', $result['items'][0]['title']);
    }
}
