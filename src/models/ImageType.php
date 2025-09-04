<?php

namespace BSBI\WebBase\models;

enum ImageType: string
{
    case SQUARE = 'Square';
    case MAIN = 'Main';
    case FIXED = 'Fixed';
    case THUMBNAIL = 'Thumbnail';
    case PANEL = 'Panel';
    case SIXTEEN_NINE = '16_9';

    // Add more types as needed
}