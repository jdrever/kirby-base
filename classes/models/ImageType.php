<?php

namespace BSBI\WebBase\models;

/**
 *
 */
enum ImageType: string
{
    case SQUARE = 'Square';
    case MAIN = 'Main';
    case FIXED = 'Fixed';
    case THUMBNAIL = 'Thumbnail';
    case PANEL = 'Panel';
    case PANEL_SMALL = 'Panel_small';
    case SIXTEEN_NINE = '16_9';
    case DEFAULT = 'Default';

    // Add more types as needed
}