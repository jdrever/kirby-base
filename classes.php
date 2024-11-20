<?php
return [
//helper classes

    'helpers\KirbyRetrievalException' => __DIR__ . '/src/helpers/KirbyRetrievalException.php',

    //traits
    'traits\CoreWebPageProperties' => __DIR__ . '/src/traits/CoreWebPageProperties.php',
    'traits\ErrorHandling' => __DIR__ . '/src/traits/ErrorHandling.php',
    'traits\ImageHandling' => __DIR__ . '/src/traits/ImageHandling.php',
    'traits\GenericKirbyHelper' => __DIR__ . '/src/traits/GenericKirbyHelper.php',
    'traits\ListHandling' => __DIR__ . '/src/traits/ListHandling.php',

    //model classes
    'models\ActionStatus' => __DIR__ . '/src/models/ActionStatus.php',
    'models\BaseFilter' => __DIR__ . '/src/models/BaseFilter.php',
    'models\BaseModel' => __DIR__ . '/src/models/BaseModel.php',
    'models\CoreLink' => __DIR__ . '/src/models/CoreLink.php',
    'models\CoreLinks' => __DIR__ . '/src/models/CoreLinks.php',
    'models\CoreLinkType' => __DIR__ . '/src/models/CoreLinkType.php',
    'models\Image' => __DIR__ . '/src/models/Image.php',
    'models\ImageType' => __DIR__ . '/src/models/ImageType.php',
    'models\Pagination' => __DIR__ . '/src/models/Pagination.php',
    'models\RelatedContent' => __DIR__ . '/src/models/RelatedContent.php',
    'models\User' => __DIR__ . '/src/models/User.php',
    'models\UserList' => __DIR__ . '/src/models/UserList.php',
    'models\WebPageLink' => __DIR__ . '/src/models/WebPageLink.php',
    'models\WebPageLinks' => __DIR__ . '/src/models/WebPageLinks.php',
    'models\WebPageBlock' => __DIR__ . '/src/models/WebPageBlock.php',
    'models\WebPageBlocks' => __DIR__ . '/src/models/WebPageBlocks.php',
];