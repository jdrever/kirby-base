<?php
/** @var string $userRole */
/** @var string $exception */
?>

<?php snippet('base/header');
 ?>
<?php snippet('logo') ?>
<div class="alert alert-danger" role="alert">
    <h2>An unexpected error has occurred </h2>
    <?php if ($userRole === 'admin') : ?>
        <p><?=$exception?></p>
    <?php endif ?>
    <a href="/" class="btn btn-outline-primary">Return to the website.</a>
</div>
</body>
</html>
