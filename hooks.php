<?php

use BSBI\WebBase\helpers\ContentIndexRegistry;
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

    // Update content indexes
    try {
        $managers = ContentIndexRegistry::getManagersForTemplate($newPage->template()->name());
        foreach ($managers as $manager) {
            $manager->indexPage($newPage, $helper);
        }
    } catch (Throwable $e) {
        error_log('Failed to update content index: ' . $e->getMessage());
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

        // Add to content indexes
        try {
            $managers = ContentIndexRegistry::getManagersForTemplate($page->template()->name());
            foreach ($managers as $manager) {
                $manager->indexPage($page, $helper);
            }
        } catch (Throwable $e) {
            error_log('Failed to add page to content index: ' . $e->getMessage());
        }

        return $page;
    },

    'page.changeStatus:after' => function ($newPage, $oldPage) {
        $helper = new KirbyInternalHelper();
        $helper->handleCaches($newPage);

        // Update search index
        try {
            $searchIndex = new SearchIndexHelper();
            $searchIndex->indexPage($newPage);
        } catch (Throwable $e) {
            error_log('Failed to update search index after status change: ' . $e->getMessage());
        }

        // Update content indexes
        try {
            $managers = ContentIndexRegistry::getManagersForTemplate($newPage->template()->name());
            foreach ($managers as $manager) {
                $manager->indexPage($newPage, $helper);
            }
        } catch (Throwable $e) {
            error_log('Failed to update content index after status change: ' . $e->getMessage());
        }
    },
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

        // Remove from content indexes
        try {
            $managers = ContentIndexRegistry::getManagersForTemplate($page->template()->name());
            foreach ($managers as $manager) {
                $manager->removePage($page->id());
            }
        } catch (Throwable $e) {
            error_log('Failed to remove page from content index: ' . $e->getMessage());
        }
    },

];