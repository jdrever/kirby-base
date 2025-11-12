<?php
/** @var string $userRole */
/** @var string $exception */
snippet('base/styles');
snippet('logo') ?>
<!DOCTYPE html>
<html lang="en"
<head>
    <meta charset="utf-8">
    <title>Error</title>
</head>
<body>
<div class="alert alert-danger" role="alert">
    <h2>An unexpected error has occurred </h2>
    <?php if ($userRole === 'admin') : ?>
        <p><?=$exception?></p>
    <?php else : ?>
    <p>This error will be reported to the website administrators.  We apologies for any incovenience.</p>
    <?php endif ?>
    <a href="/" class="btn btn-outline-primary">Return to the website.</a>
</div>
</body>
</html>
