<?php

use BSBI\Web\helpers\KirbyHelper;

return [
    'page.update:after' => function ($newPage, $oldPage) {
        $user = kirby()->user();

        $newPage = $newPage->update([
            'updatedDate' => date('Y-m-d H:i:s'),
            'updatedBy' => $user?->id()
        ]);
        if ($newPage->publishedDate()->isEmpty()) {
            $newPage = $newPage->update([
                'publishedDate' => date('Y-m-d H:i:s'),
                'publishedBy' => $user?->id()
            ]);
        }
        $helper = new KirbyHelper(kirby(), kirby()->site(), kirby()->page());
        $helper->handleTwoWayTagging($newPage, $oldPage);
        $helper->handleCaches($newPage);

        return $newPage;
    },


    'page.create:after' => function ($page) {
        if ($page->publishedDate()->isEmpty() || $page->publishedBy()->isEmpty()) {
            $user = kirby()->user();
            $page = $page->update([
                'publishedDate' => date('Y-m-d H:i:s'),
                'publishedBy' => $user?->id()
            ]);
        }

        $helper = new KirbyHelper(kirby(), kirby()->site(), kirby()->page());
        $helper->handleTwoWayTagging($page);
        $helper->handleCaches($page);
        return $page;
    },
];