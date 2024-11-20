<?php

namespace BSBI\WebBase\models;

enum ImageType: string
{
    case SQUARE = 'Square';
    case MAIN = 'Main';

    case FIXED = 'Fixed';

    // Add more types as needed
}