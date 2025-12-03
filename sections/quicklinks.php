<?php
// site/sections/quicklinks.php (TEMPORARY TEST CODE)

return [
    'props' => [
        'headline' => function ($headline = 'Quick Links') {
            return $headline;
        }
    ],
    'computed' => [
        // This is the data that will be sent to the Vue component's 'load()' method
        'links' => function () {
            // Get the structure field data from the site object (assuming 'quicklinks' is the field name)
            $quicklinks = $this->kirby()->site()->quicklinks();

            if (empty($quicklinks) || $quicklinks->isEmpty()) {
                return [];
            }

            $links = [];
            foreach ($quicklinks->toStructure() as $item) {
                $page = $item->linkPage()->toPage();

                if ($page) {
                    $links[] = [
                        'text' => $item->linkText()->isNotEmpty()
                            ? $item->linkText()->value()
                            : $page->title()->value(),
                        // Use panelUrl() for the internal Panel link
                        'url'  => $page->panel()->url(),
                        'info' => $page->slug()
                    ];
                }
            }
            return $links;
        }
    ],

];