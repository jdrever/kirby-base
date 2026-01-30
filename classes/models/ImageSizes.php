<?php

namespace BSBI\WebBase\models;

/**
 * Enum for responsive image sizes attribute values.
 * These help browsers select the appropriate image from srcset based on viewport width.
 */
enum ImageSizes: string
{
    /** No sizes attribute specified - browser uses default behavior */
    case NOT_SPECIFIED = '';

    /** Image takes half width on large screens (>=768px), full width on mobile */
    case HALF_LARGE_SCREEN = '(min-width: 768px) 50vw, 100vw';

    /** Image takes one-third width on large screens (>=992px), half on medium, full on mobile */
    case THIRD_LARGE_SCREEN = '(min-width: 992px) 33vw, (min-width: 768px) 50vw, 100vw';

    /** Image takes one-quarter width on large screens (>=992px), third on medium, half on small */
    case QUARTER_LARGE_SCREEN = '(min-width: 992px) 25vw, (min-width: 768px) 33vw, 50vw';

    /** Full-bleed hero image - always full viewport width */
    case FULL_BLEED = '100vw';

    /** Card layout - fixed max width with responsive fallback */
    case CARD = '(min-width: 992px) 350px, (min-width: 768px) 50vw, 100vw';

    /** Thumbnail - small fixed size */
    case THUMBNAIL = '(min-width: 768px) 100px, 80px';

    /** Content area image - typical article width */
    case CONTENT = '(min-width: 992px) 700px, (min-width: 768px) calc(100vw - 40px), calc(100vw - 20px)';
}