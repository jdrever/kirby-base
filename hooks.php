<?php

use BSBI\WebBase\helpers\KirbyInternalHelper;
use BSBI\WebBase\helpers\SearchIndexHelper;

function handlePageChange($newPage, $oldPage) {
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
    /** @noinspection PhpUnhandledExceptionInspection */
    $helper = new KirbyInternalHelper();
    /** @noinspection PhpUnhandledExceptionInspection */
    $helper->handleTwoWayTagging($newPage, $oldPage);
    $helper->handleCaches($newPage);

    // Update search index
    try {
        $searchIndex = new SearchIndexHelper();
        $searchIndex->indexPage($newPage);
    } catch (Throwable $e) {
        error_log('Failed to update search index: ' . $e->getMessage());
    }

    return $newPage;
}

return [
    'page.update:after' => function ($newPage, $oldPage) {
        return handlePageChange($newPage, $oldPage);
    },

    'page.changeTitle:after' => function ($newPage, $oldPage) {
        return handlePageChange($newPage, $oldPage);
    },

    'page.changeSlug:after' => function ($newPage, $oldPage) {
        return handlePageChange($newPage, $oldPage);
    },


    'page.create:after' => function ($page) {
        if ($page->publishedDate()->isEmpty() || $page->publishedBy()->isEmpty()) {
            $user = kirby()->user();
            $page = $page->update([
                'publishedDate' => date('Y-m-d H:i:s'),
                'publishedBy' => $user?->id()
            ]);
        }

        $helper = new KirbyInternalHelper();
        $helper->handleTwoWayTagging($page);
        $helper->handleCaches($page);

        // Add to search index
        try {
            $searchIndex = new SearchIndexHelper();
            $searchIndex->indexPage($page);
        } catch (Throwable $e) {
            error_log('Failed to add page to search index: ' . $e->getMessage());
        }

        return $page;
    },

    //'page.changeStatus:after' => function ($newPage, $oldPage) {
    //    $helper = new KirbyHelper(kirby(), kirby()->site(), kirby()->page());
    //    //$helper->handleCaches($newPage);
    //},
    'page.delete:before' => function (Kirby\Cms\Page $page) {
        $helper = new KirbyInternalHelper();
        $helper->handleCaches($page);

        // Remove from search index
        try {
            $searchIndex = new SearchIndexHelper();
            $searchIndex->removePage($page->id());
        } catch (Throwable $e) {
            error_log('Failed to remove page from search index: ' . $e->getMessage());
        }
    },

];