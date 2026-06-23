<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\testing;

use BSBI\WebBase\Testing\KirbyTestEnvironment;
use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

/**
 * Tests for KirbyTestEnvironment.
 *
 * The Apps are booted in setUpBeforeClass, not in the test methods. Booting a
 * Kirby App registers global error/exception handlers it never restores; doing
 * that inside a test method is exactly the leak this class exists to avoid (and
 * PHPUnit reports it as risky). Booting up front keeps it out of the per-test
 * measurement window — which is also the lesson the class teaches its callers.
 */
final class KirbyTestEnvironmentTest extends TestCase
{
    private static App $kirby;
    private static App $kirbyWithOptions;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$kirbyWithOptions = KirbyTestEnvironment::boot('kbtest-env-options', ['custom.option' => 'value']);
        // Booted last, so this is the current Kirby singleton.
        self::$kirby = KirbyTestEnvironment::boot('kbtest-env');
    }

    public function testBootRegistersAppAsSingleton(): void
    {
        // kirby() resolves to the booted instance rather than auto-creating one
        $this->assertSame(self::$kirby, App::instance(null, true));
    }

    public function testBootUsesNamespacedIndexRoot(): void
    {
        $this->assertStringEndsWith('kbtest-env', self::$kirby->root('index'));
    }

    public function testBootCreatesContentAndCacheRoots(): void
    {
        $this->assertDirectoryExists(self::$kirby->root('content'));
        $this->assertDirectoryExists(self::$kirby->root('cache'));
    }

    public function testBootMergesExtraOptions(): void
    {
        $this->assertSame('value', self::$kirbyWithOptions->option('custom.option'));
    }
}
