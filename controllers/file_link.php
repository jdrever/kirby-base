<?php

declare(strict_types=1);

use BSBI\WebBase\helpers\KirbyInternalHelper;

return function ($page) {
    $helper = new KirbyInternalHelper();
    // Serve the file inline so the file_link page URL is retained (no redirect,
    // no forced download).
    $helper->redirectToFile($page, 'file', true);
};
