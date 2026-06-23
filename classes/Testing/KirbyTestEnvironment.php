<?php

declare(strict_types=1);

namespace BSBI\WebBase\Testing;

use Kirby\Cms\App;

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
}
