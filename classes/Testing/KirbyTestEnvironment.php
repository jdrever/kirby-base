<?php

declare(strict_types=1);

namespace BSBI\WebBase\Testing;

use FilesystemIterator;
use InvalidArgumentException;
use Kirby\Cms\App;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Boots a minimal, in-memory Kirby App for tests.
 *
 * This is PHPUnit-agnostic on purpose: it must not depend on phpunit so it can
 * ship in the (non-dev) plugin autoload and be consumed by any site that uses
 * kirby-base. Test frameworks compose it; they do not extend it.
 *
 * Why a shared, deliberately-booted App matters: Kirby's global kirby() helper
 * auto-creates an App when none exists, and that boot registers global error and
 * exception handlers it never restores — which PHPUnit 12 reports as a risky
 * test. Booting once, up front, keeps that out of any per-test measurement
 * window and gives cache-backed code a real, temp-backed cache to talk to.
 */
final class KirbyTestEnvironment
{
    /** @var array<string> Temp dirs created by bootWithContent, removed at shutdown. */
    private static array $tempDirs = [];

    private static bool $cleanupRegistered = false;

    /**
     * Boots a minimal Kirby App against throwaway temp roots and returns it.
     * Booting registers the App as Kirby's singleton, so kirby()/site() resolve
     * to it for the duration of the test run.
     *
     * @param string $namespace A unique temp-dir name; one per test class is fine.
     * @param array<string, mixed> $options Extra Kirby options to merge in.
     * @return App The booted Kirby application.
     */
    public static function boot(string $namespace = 'kirby-base-tests', array $options = []): App
    {
        $root = sys_get_temp_dir() . '/' . $namespace;

        foreach ([$root . '/content', $root . '/cache'] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return new App([
            'roots' => [
                'index'   => $root,
                'content' => $root . '/content',
                'cache'   => $root . '/cache',
            ],
            'options' => $options,
        ]);
    }

    /**
     * Boots a Kirby App against a copy of a committed fixture content tree, so
     * tests can exercise content-tree-traversing code (page lookups, parent/child,
     * collections) and code that writes pages (the copy lives in a writable temp
     * dir, removed automatically at shutdown).
     *
     * Call this from setUpBeforeClass (not a test method): booting registers global
     * handlers, which PHPUnit flags as risky if it happens inside a test.
     *
     * @param string $fixtureDir Absolute path to the fixture content directory.
     * @param string $namespace A short label for the temp dir (a unique suffix is added).
     * @param array<string, mixed> $extra Extra Kirby config merged in (e.g. `blueprints`,
     *        `collections`, `options`) — these are site-specific and stay with the caller.
     * @return App The booted Kirby application.
     * @throws InvalidArgumentException If the fixture directory does not exist.
     */
    public static function bootWithContent(
        string $fixtureDir,
        string $namespace = 'kirby-base-content-tests',
        array $extra = []
    ): App {
        if (!is_dir($fixtureDir)) {
            throw new InvalidArgumentException("Fixture content directory not found: {$fixtureDir}");
        }

        $root    = sys_get_temp_dir() . '/' . $namespace . '-' . uniqid();
        $content = $root . '/content';
        self::copyDir($fixtureDir, $content);
        // Create the cache root explicitly, mirroring boot(), so cache-backed code
        // never depends on Kirby auto-creating it.
        mkdir($root . '/cache', 0777, true);

        self::registerCleanup();
        self::$tempDirs[] = $root;

        $config = array_replace_recursive(
            [
                'roots' => [
                    'index'   => $root,
                    'content' => $content,
                    'cache'   => $root . '/cache',
                ],
            ],
            $extra
        );

        return new App($config);
    }

    private static function copyDir(string $src, string $dest): void
    {
        mkdir($dest, 0777, true);

        /** @var SplFileInfo $item */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . substr($item->getPathname(), strlen($src) + 1);
            if ($item->isDir()) {
                mkdir($target, 0777, true);
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private static function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        /** @var SplFileInfo $item */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    private static function registerCleanup(): void
    {
        if (self::$cleanupRegistered) {
            return;
        }
        self::$cleanupRegistered = true;
        register_shutdown_function(static function (): void {
            foreach (self::$tempDirs as $dir) {
                self::removeDir($dir);
            }
        });
    }
}
