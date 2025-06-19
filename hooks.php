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
                'publishedBy' => $user ? $user->id() : null
            ]);
        }

        //if ($newPage->template()->name() === 'vacancy') {
            $helper = new KirbyHelper(kirby(), kirby()->site(), kirby()->page());
            $helper->handleTwoWayTagging($newPage, $oldPage);
        //}

        return $newPage;
    },
    'page.create:after' => function ($page) {
        $user = kirby()->user();
        $page = $page->update([
            'publishedDate' => date('Y-m-d H:i:s'),
            'publishedBy' => $user?->id()
        ]);
        return $page;
    },
];