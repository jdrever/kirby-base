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
            kirby()->impersonate('kirby');
            $helper = new KirbyInternalHelper();
            return $helper->publishScheduledPages();
        }
    ]
];