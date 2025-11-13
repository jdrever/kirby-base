<?php

use BSBI\WebBase\helpers\KirbyRetrievalException;

?>
<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php if (!isset($pages)) :
    throw new Exception('No $page supplied');
endif;

if (!isset($ignore)) :
    $ignore = [];
endif;

    foreach ($pages as $p): ?>
        <?php
        // Skip pages explicitly ignored in config
        if (in_array($p->uri(), $ignore)) continue;
        if (\Kirby\Toolkit\Str::startsWith($p->uri(), 'members/')) continue;
        // Skip pages with a custom field flagging them as 'noindex'
        if ($p->meta_robots()->exists() && $p->meta_robots()->value() === 'noindex') continue;
        ?>
        <url>
            <loc><?= html($p->url()) ?></loc>

            <?php
            // Use the last modified date for accurate crawling signals
            // 'c' format outputs the date in the required ISO 8601 format
            ?>
            <lastmod><?= $p->modified('c', 'date') ?></lastmod>

            <?php
            // Priority is often ignored by Google, but can be helpful for initial context
            // This calculates priority based on depth (home page = 1, deeper pages = lower)
            $priority = $p->isHomePage() ? 1.0 : number_format(0.5 / $p->depth(), 1);
            ?>
            <priority><?= $priority ?></priority>
        </url>
    <?php endforeach ?>
</urlset>