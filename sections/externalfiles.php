<?php

use Kirby\Cms\Url;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

return
[
    // Define the data that the Vue component will receive
    'props' => [
        'headline' => fn ($headline = 'Files') => $headline,
        'path'     => fn ($path) => $path, // The relative path to your external folder
    ],
    // The API logic to fetch the files
    'api' => function () {
        $path = $this->path();

        if (is_null($path)) {
            return [ 'items' => [] ];
        }

        $absolutePath = kirby()->root('index') . $path;
        $baseURL = Url::index() . 'externalfiles.php/' . $path . '/';

        $files = [];
        if (is_dir($absolutePath)) {
            foreach (Dir::read($absolutePath) as $filename) {
                // Exclude hidden files
                if (str_starts_with($filename, '.')) continue;

                $files[] = [
                    'id'       => $filename,
                    'text'     => $filename,
                    'url'      => $baseURL . rawurlencode($filename), // Direct download URL
                    'info'     => F::extension($filename), // Show file extension as info
                    'link'     => $baseURL . rawurlencode($filename),
                ];
            }
        }

        return [
            'items' => $files
        ];
    }
];