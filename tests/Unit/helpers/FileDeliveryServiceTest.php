<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\FileDeliveryService;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FileDeliveryService, which decides whether a file is served inline
 * (page URL retained, browser displays it) or as a forced download.
 */
final class FileDeliveryServiceTest extends TestCase
{
    private static string $file;

    public static function setUpBeforeClass(): void
    {
        self::$file = sys_get_temp_dir() . '/file-delivery-' . uniqid() . '.pdf';
        file_put_contents(self::$file, '%PDF-1.4 fake pdf bytes');
    }

    public static function tearDownAfterClass(): void
    {
        if (is_file(self::$file)) {
            unlink(self::$file);
        }
    }

    public function testInlineResponseServesFileWithoutAttachmentDisposition(): void
    {
        $response = (new FileDeliveryService())->buildResponse(self::$file, 'download.pdf', false);

        $this->assertSame('application/pdf', $response->type());
        $this->assertArrayNotHasKey('Content-Disposition', $response->headers());
        $this->assertSame('%PDF-1.4 fake pdf bytes', $response->body());
    }

    public function testForcedDownloadSendsAttachmentDispositionWithFilename(): void
    {
        $response = (new FileDeliveryService())->buildResponse(self::$file, 'newsletter.pdf', true);

        $this->assertSame(
            'attachment; filename="newsletter.pdf"',
            $response->headers()['Content-Disposition'] ?? null
        );
        $this->assertSame('%PDF-1.4 fake pdf bytes', $response->body());
    }
}
