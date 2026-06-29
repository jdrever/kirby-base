<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use BSBI\WebBase\helpers\AssetVersioner;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AssetVersioner: deterministic mtime-based cache-busting of asset URLs.
 */
final class AssetVersionerTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/asset-versioner-' . uniqid();
        mkdir($this->root . '/assets/css', 0o777, true);
        mkdir($this->root . '/assets/js', 0o777, true);
    }

    protected function tearDown(): void
    {
        @unlink($this->root . '/assets/css/custom.css');
        @unlink($this->root . '/assets/js/bootstrap.js');
        @rmdir($this->root . '/assets/css');
        @rmdir($this->root . '/assets/js');
        @rmdir($this->root . '/assets');
        @rmdir($this->root);
    }

    public function testAppendsMtimeVersionWhenFileExists(): void
    {
        $file = $this->root . '/assets/css/custom.css';
        file_put_contents($file, 'body{}');
        touch($file, 1_700_000_000);

        $versioner = new AssetVersioner($this->root);

        self::assertSame(
            '/assets/css/custom.css?v=1700000000',
            $versioner->versioned('/assets/css/custom.css')
        );
    }

    public function testReturnsPathUnchangedWhenFileMissing(): void
    {
        $versioner = new AssetVersioner($this->root);

        self::assertSame(
            '/assets/js/missing.js',
            $versioner->versioned('/assets/js/missing.js')
        );
    }

    public function testHandlesDocumentRootWithTrailingSlash(): void
    {
        $file = $this->root . '/assets/js/bootstrap.js';
        file_put_contents($file, 'console.log(1)');
        touch($file, 1_700_000_001);

        $versioner = new AssetVersioner($this->root . '/');

        self::assertSame(
            '/assets/js/bootstrap.js?v=1700000001',
            $versioner->versioned('/assets/js/bootstrap.js')
        );
    }

    public function testReturnsPathUnchangedWhenDocumentRootIsNull(): void
    {
        $versioner = new AssetVersioner(null);

        self::assertSame(
            '/assets/css/custom.css',
            $versioner->versioned('/assets/css/custom.css')
        );
    }

    public function testUsesAmpersandWhenPathAlreadyHasQuery(): void
    {
        $file = $this->root . '/assets/css/custom.css';
        file_put_contents($file, 'body{}');
        touch($file, 1_700_000_002);

        $versioner = new AssetVersioner($this->root);

        self::assertSame(
            '/assets/css/custom.css?theme=dark&v=1700000002',
            $versioner->versioned('/assets/css/custom.css?theme=dark')
        );
    }
}
