<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;

/**
 * All form_submission pages across the entire site.
 *
 * Used by the form_submissions content index for rebuilds.
 *
 * @param Site $site
 * @param App $kirby
 * @return Pages
 */
return function (Site $site, App $kirby): Pages {
    return $site->index()->filter(fn ($page) => $page->intendedTemplate()->name() === 'form_submission');
};
