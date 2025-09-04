<?php

declare(strict_types=1);

use BSBI\WebBase\helpers\KirbyInternalHelper;

return function ($page, $pages, $site, $kirby) {
    $helper = new KirbyInternalHelper($kirby, $site, $page);
    $helper->redirectToPage($page);
};