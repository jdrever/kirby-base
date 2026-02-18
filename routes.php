<?php

use BSBI\WebBase\helpers\KirbyInternalHelper;
use BSBI\WebBase\helpers\SearchIndexHelper;
use Kirby\Cms\Page;
use Kirby\Http\Response;
use Kirby\Toolkit\Tpl;

return [
    [
        'pattern' => 'form-export/(:all)',
        'method'  => 'GET',
        'action'  => function (string $pageId): Response {
            $helper = new KirbyInternalHelper();

            if (!$helper->isCurrentUserAdminOrEditor()) {
                return new Response(
                    'You must be an administrator or editor to export submissions.',
                    'text/plain',
                    403
                );
            }

            $page = page($pageId);
            if (!$page) {
                return new Response('Page not found.', 'text/plain', 404);
            }

            $submissions = $page->children()->template('form_submission');

            // Pass 1: collect all unique questions across every submission,
            // preserving the first-seen order so later submissions with
            // extra questions simply append new columns at the right.
            $allQuestions = [];
            $submissionRows = [];

            foreach ($submissions as $submission) {
                $rowData = ['_title' => $submission->title()->value()];

                foreach ($submission->submission()->toStructure() as $item) {
                    $question = $item->question()->value();
                    $answer   = $item->answer()->value();

                    if (!in_array($question, $allQuestions, true)) {
                        $allQuestions[] = $question;
                    }

                    $rowData[$question] = $answer;
                }

                $submissionRows[] = $rowData;
            }

            // Pass 2: build the CSV in memory.
            ob_start();
            $handle = fopen('php://output', 'w');

            // Row 1: form page title (identification row).
            fputcsv($handle, [$page->title()->value()]);

            // Row 2: column headers.
            fputcsv($handle, array_merge(['Submission'], $allQuestions));

            // Data rows: one per submission, answers mapped to the correct column.
            foreach ($submissionRows as $row) {
                $csvRow = [$row['_title']];
                foreach ($allQuestions as $question) {
                    $csvRow[] = $row[$question] ?? '';
                }
                fputcsv($handle, $csvRow);
            }

            fclose($handle);
            $csv = ob_get_clean();

            $filename = 'submissions-' . $page->slug() . '-' . date('Y-m-d') . '.csv';

            return new Response($csv, 'text/csv', 200, [
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            ]);
        }
    ],
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