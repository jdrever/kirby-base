<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Appends a deterministic, content-derived version query to static asset URLs so
 * that a long-lived (immutable) browser/CDN cache is only bypassed when the
 * underlying file actually changes.
 *
 * The version token is the file's last-modified time (filemtime): an unchanged
 * file keeps the same URL and therefore stays cached, while a rebuilt/redeployed
 * file gets a fresh URL that misses the cache exactly once. This avoids the need
 * to manually purge the CDN after every CSS/JS deploy, without weakening the
 * long max-age applied to assets.
 */
final readonly class AssetVersioner
{
    /**
     * @param string|null $documentRoot Absolute filesystem path that web-root-relative
     *                                  asset paths are resolved against (typically the
     *                                  Kirby index root containing the /assets directory).
     *                                  Null (e.g. an unresolved root) disables versioning.
     */
    public function __construct(private ?string $documentRoot)
    {
    }

    /**
     * Return the asset path with an mtime-based "?v=" cache-busting query.
     *
     * If the file cannot be found on disk the path is returned unchanged, so a
     * missing or unresolved asset never breaks the markup — it simply is not
     * versioned.
     *
     * @param string $assetPath Web-root-relative asset path, e.g. "/assets/css/custom.css".
     * @return string The same path with "?v=<mtime>" appended when resolvable.
     */
    public function versioned(string $assetPath): string
    {
        if ($this->documentRoot === null) {
            return $assetPath;
        }

        $queryPosition = strpos($assetPath, '?');
        $filePath = $queryPosition === false ? $assetPath : substr($assetPath, 0, $queryPosition);

        $absolutePath = rtrim($this->documentRoot, '/') . '/' . ltrim($filePath, '/');
        $modifiedTime = @filemtime($absolutePath);

        if ($modifiedTime === false) {
            return $assetPath;
        }

        $separator = $queryPosition === false ? '?' : '&';

        return $assetPath . $separator . 'v=' . $modifiedTime;
    }
}
