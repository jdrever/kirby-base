<?php

namespace BSBI\WebBase\models;

/**
 *
 */
enum ImageSizes: string
{
    case NOT_SPECIFIED = '';
    case HALF_LARGE_SCREEN = '(min-width: 768px) 50vw, 100vw';


    // Add more types as needed
}