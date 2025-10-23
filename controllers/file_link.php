<?php

declare(strict_types=1);

use BSBI\WebBase\helpers\KirbyInternalHelper;

return function ($page) {
    $helper = new KirbyInternalHelper();
    $helper->redirectToFile($page);
};
