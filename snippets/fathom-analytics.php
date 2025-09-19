<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

if (!isset($dataSite)) :
    throw new Exception('No dataSite supplied');
endif; ?>


<script src="https://cdn.usefathom.com/script.js" data-site="<?= $dataSite ?>" defer></script>