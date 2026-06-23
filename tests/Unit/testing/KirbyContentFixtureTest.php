<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\testing;

use BSBI\WebBase\Testing\KirbyTestEnvironment;
use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

/**
 * Tests KirbyTestEnvironment::bootWithContent against a fixture content tree.
 *
 * Booted in setUpBeforeClass (so it stays out of the per-test risky-handler
 * window) and as the only App in this class, so it is the current Kirby singleton
 * — page()/content resolution on a non-singleton App falls through to the global
 * instance, so the App under assertion must be the singleton.
 */
final class KirbyContentFixtureTest extends TestCase
{
    private static App $kirby;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$kirby = KirbyTestEnvironment::bootWithContent(
            __DIR__ . '/../../fixtures/content-sample',
            'kbtest-content'
        );
    }

    public function testFixturePageIsResolvable(): void
    {
        $page = self::$kirby->page('sample');

        $this->assertNotNull($page);
        $this->assertSame('sample', $page->id());
    }

    public function testFixturePageFieldsAreReadable(): void
    {
        $page = self::$kirby->page('sample');

        $this->assertSame('ABC123', $page->content()->get('code')->value());
    }

    public function testContentRootIsWritable(): void
    {
        // The fixture is copied to a temp dir so tests that create/update pages work.
        $this->assertDirectoryIsWritable(self::$kirby->root('content'));
    }
}
