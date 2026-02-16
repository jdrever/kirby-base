<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\Document;
use BSBI\WebBase\models\Documents;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Document and Documents models.
 *
 * Covers Document size defaults, modified date formatting,
 * and Documents list add/retrieve/empty-state behaviour.
 */
final class DocumentTest extends TestCase
{
    /**
     * Create a Document with sensible defaults for testing.
     *
     * @param string $title The document title
     * @param string $url   The document URL
     * @return Document
     */
    private function createDocument(string $title = 'Report.pdf', string $url = '/files/report.pdf'): Document
    {
        return new Document($title, $url);
    }

    // --- Document ---

    /**
     * Verify that size defaults to 'Unknown' when not explicitly set.
     */
    public function testSizeDefaultsToUnknown(): void
    {
        $doc = $this->createDocument();

        $this->assertSame('Unknown', $doc->getSize());
    }

    /**
     * Verify size can be set and retrieved.
     */
    public function testSizeGetterSetter(): void
    {
        $doc = $this->createDocument();
        $doc->setSize('2.5 MB');

        $this->assertSame('2.5 MB', $doc->getSize());
    }

    /**
     * Verify modified date can be set and retrieved as a DateTime instance.
     */
    public function testModifiedDateGetterSetter(): void
    {
        $doc = $this->createDocument();
        $date = new DateTime('2024-06-15 10:30:00');
        $doc->setModifiedDate($date);

        $this->assertSame($date, $doc->getModifiedDate());
    }

    /**
     * Verify getFormattedModifiedDate() returns the date in 'd/m/Y H:i:s' format.
     */
    public function testFormattedModifiedDate(): void
    {
        $doc = $this->createDocument();
        $doc->setModifiedDate(new DateTime('2024-06-15 10:30:45'));

        $this->assertSame('15/06/2024 10:30:45', $doc->getFormattedModifiedDate());
    }

    // --- Documents list ---

    /**
     * Verify documents can be added to a list and retrieved in order.
     */
    public function testDocumentsAddAndRetrieve(): void
    {
        $list = new Documents();
        $doc1 = $this->createDocument('File1.pdf');
        $doc2 = $this->createDocument('File2.pdf');

        $list->addListItem($doc1);
        $list->addListItem($doc2);

        $this->assertSame(2, $list->count());
        $this->assertSame($doc1, $list->getListItems()[0]);
        $this->assertSame($doc2, $list->getListItems()[1]);
    }

    /**
     * Verify a new Documents list is empty by default.
     */
    public function testDocumentsEmptyByDefault(): void
    {
        $list = new Documents();

        $this->assertSame(0, $list->count());
        $this->assertFalse($list->hasListItems());
    }
}
