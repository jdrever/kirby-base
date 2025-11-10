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
];