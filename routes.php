<?php

use BSBI\WebBase\helpers\KirbyInternalHelper;

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
        'action'  => function () {
            $helper = new KirbyInternalHelper();
            return $helper->publishScheduledPages();
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
            $content = snippet('robots',[], true);
            return new Kirby\Cms\Response($content, 'text/plain');
        }
    ],

];