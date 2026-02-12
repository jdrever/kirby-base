<?php

use BSBI\WebBase\helpers\KirbyInternalHelper;
use BSBI\WebBase\helpers\SearchIndexHelper;
use Kirby\Cms\Page;
use Kirby\Http\Response;
use Kirby\Toolkit\Tpl;

return [
    [
        'pattern' => 'logout',
        'action' => function () {
            if ($user = kirby()->user()) {
                $user->logout();
            }

            go('login');
        }
    ],
    [
        'pattern' => 'scheduled-publish',
        'method'  => 'GET|POST',
        'action'  => function () {
            $helper = new KirbyInternalHelper();
            $output = $helper->publishScheduledPages();
            return new Kirby\Cms\Response(
                $output,
                'text/plain',
                200
            );
        }
    ],
    [
        'pattern' => 'cookie-consent',
        'method' => 'POST',
        'action' => function () {
            $helper = new KirbyInternalHelper();
            $helper->processCookieConsent();
        }
    ],
    [
        'pattern' => 'sitemap.xml',
        'action' => function() {
            $pages = site()->pages()->index()->filter(function($p) {
                return $p->isListed() || $p->isInvisible();
            });
            $ignore = kirby()->option('sitemapExclude', ['error']);
            $content = snippet('sitemap', compact('pages', 'ignore'), true);

            return new Kirby\Cms\Response($content, 'application/xml');
        }
    ],
    [
        'pattern' => 'robots.txt',
        'action' => function() {
            $content = snippet('robots-txt',[], true);
            return new Kirby\Cms\Response($content, 'text/plain');
        }
    ],
    [
        'pattern' => '500',
        'action' => function () {
            $exceptionAsString = kirby()->session()->pull('exceptionAsString');

            echo Tpl::load(__DIR__ . '/templates/error-500.php', [
                'userRole' => kirby()->user() ? kirby()->user()->role()->name() : '',
                'exception' => $exceptionAsString,
            ]);
            exit();
        },
    ],
    [
        'pattern' => 'files/(:any)',
        'action'  => function ($slug) {
            // Find the archive page first to narrow the search
            $archivePage = page('file-archive'); // Adjust to your actual page URI

            if ($archivePage) {
                // Search files for a match in the 'alt_slug' field
                $file = $archivePage->files()->findBy('permanentUrl', $slug);

                if ($file) {
                    // Redirect to the actual physical file URL
                    return go($file->mediaUrl());
                }
            }

            // If no file matches, show 404
            return site()->errorPage();
        }
    ],
    [
        'pattern' => 'search-rebuild',
        'method' => 'GET',
        'action' => function () {
            $helper = new KirbyInternalHelper();
            if ($helper->isCurrentUserAdminOrEditor()) {
                try {
                    $searchIndex = new SearchIndexHelper();
                    $result = $searchIndex->rebuildIndex();
                    $searchCount = $result['search_index'];
                    $allPagesCount = $result['all_pages'];
                    return new Response(
                        "Search index rebuilt successfully. Indexed $searchCount pages for site search, $allPagesCount pages for panel search.",
                        'text/plain',
                        200
                    );
                } catch (Exception $e) {
                    return new Response(
                        'Failed to rebuild search index: ' . $e->getMessage(),
                        'text/plain',
                        500
                    );
                }
            }
            return new Response('You must be an administrator to access this page.', 'text/plain', 403);
        }
    ],
    [
        'pattern' => 'search-stats',
        'method' => 'GET',
        'action' => function () {
            $helper = new KirbyInternalHelper();
            if ($helper->isCurrentUserAdminOrEditor()) {
                try {
                    $searchIndex = new SearchIndexHelper();
                    $stats = $searchIndex->getStats();
                    return new Response(
                        json_encode($stats, JSON_PRETTY_PRINT),
                        'application/json',
                        200
                    );
                } catch (Exception $e) {
                    return new Response(
                        json_encode(['error' => $e->getMessage()]),
                        'application/json',
                        500
                    );
                }
            }
            return new Response('You must be an administrator to access this page.', 'text/plain', 403);
        }
    ],
];