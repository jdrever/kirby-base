<?php

declare(strict_types=1);

use BSBI\Docs\models\WebPage;
use BSBI\Docs\models\CoreLinkType;

if (!isset($currentPage)) :
    throw new Exception('colour-mode snippet: $currentPage not provided');
endif;

/**
 * @var WebPage $currentPage
 */

$menuPages = $currentPage->getMenuPages();

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary p-0">
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBar"
    aria-controls="navBar" aria-expanded="false" aria-label="Menu">
    <span class="navbar-toggler-icon" style="font-size:.8em"></span><span class="px-1"
      style="font-size: .8em;">Menu</span>
  </button>
  <div class="collapse navbar-collapse m-0 p-0" id="navBar">
    <ul class="navbar-nav ms-2">
      <li class="nav-item border-end"><a href="https://bsbi.org/" class="nav-link text-white ms-2 px-2">&larr; BSBI Home</a></li>
      <li class="nav-item"><a href="/" class="nav-link text-white ms-2 px-2">Home</a></li>
      <?php 

foreach ($menuPages->getListItems() as $item) :
?>
      <li class="nav-item"><a href="<?=$item->getUrl()?>" class="nav-link text-white ms-2 px-2"><?=$item->getTitle()?></a></li>
<?php
endforeach; ?>
    </ul>
    <div class="ms-auto d-flex align-items-center" id="searchBar">
        <form method="get" action="../../../../index.php" role="search">
            <input type="search" aria-label="Search" name="q" class="form-control-sm ms-2 me-2">
            <button class="btn btn-sm btn-success me-1" type="submit">Search</button>
        </form>
      <?php snippet('core-link', ['coreLinkType'=> CoreLinkType::SETTINGS_PAGE,
          'class' => "settings nav-link text-white ms-2 px-2",
          'title' => 'Settings']);  ?>
    </div>
  </div>
</nav> 

 
