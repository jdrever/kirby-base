<?php

use BSBI\WebBase\helpers\KirbyInternalHelper;
use Kirby\Cms\Page;
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
        }
    ]

];