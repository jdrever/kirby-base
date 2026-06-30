<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

use Exception;
use Kirby\Http\Response;

/**
 * Builds HTTP responses that serve a file's bytes in-place (keeping the current
 * page URL) instead of redirecting the browser to the file's media URL.
 *
 * Deliberately takes primitive values (path + filename) rather than a Kirby File
 * so the disposition/mime logic can be unit-tested without booting Kirby.
 */
final readonly class FileDeliveryService
{
    /**
     * Build a response that streams a file back to the browser.
     *
     * @param string $root The absolute path to the file on disk
     * @param string $filename The filename to advertise when forcing a download
     * @param bool $forceDownload When true, send a download (attachment) response;
     *                            when false, serve the file inline (e.g. PDF/image
     *                            opens in the browser)
     * @return Response Inline file response, or a forced-download response
     * @throws Exception If a forced download is requested but the file does not exist
     */
    public function buildResponse(string $root, string $filename, bool $forceDownload): Response
    {
        return $forceDownload
            ? Response::download($root, $filename)
            : Response::file($root);
    }
}
