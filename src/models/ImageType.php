<?php

namespace BSBI\WebBase\models;

enum ImageType: string
{
    case SQUARE = 'Square';
    case MAIN = 'Main';
    case FIXED = 'Fixed';
    case THUMBNAIL = 'Thumbnail';
    case PANEL = 'Panel';

    // Add more types as needed
}