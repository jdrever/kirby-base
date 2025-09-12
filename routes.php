<?php
return [
    [
        'pattern' => 'logout',
        'action' => function () {
            if ($user = kirby()->user()) {
                $user->logout();
            }

            go('login');
        }
    ],
    [
        'pattern' => 'scheduled-publish',
        'action'  => function () {
            $site = kirby()->site();
            $scheduledEntries = $site->scheduled()->toStructure();
            $updatedList = [];
            $publishedCount = 0;

            foreach ($scheduledEntries as $entry) {
                $pageId = $entry->page()->toPage(); // Get the Page object from the field
                $scheduledDate = $entry->scheduledPublishDate()->value();
                $scheduledTime = $entry->scheduledPublishTime()->value();

                // If a page and a date/time exist for the entry
                if ($pageId && $scheduledDate && $scheduledTime) {
                    // Combine the date and time into a single string
                    $timezone = new \DateTimeZone('Europe/London');
                    $scheduledDateTime = new \DateTime(
                        $scheduledDate . ' ' . $scheduledTime,
                        $timezone
                    );

                    // Convert the string to a Unix timestamp
                    $currentDateTime = new \DateTime('now', $timezone);



                    // Debugging: Check the values
                    // echo "Page: " . $page->id() . "<br>";
                    // echo "Scheduled Timestamp: " . $scheduledTimestamp . "<br>";
                    // echo "Current Timestamp: " . time() . "<br>";

                    // The comparison should now work reliably
                    if ($currentDateTime >= $scheduledDateTime) {
                        try {
                            $page = kirby()->page($pageId);
                            if ($page) {
                                $page->changeStatus('listed');
                                $publishedCount++;
                            }
                        } catch (\Exception $e) {
                            // Log or handle the error
                        }
                    } else {
                        // Keep the entry in the list if it's not ready to be published
                        $updatedList[] = $entry->content()->toArray();
                    }
                }
            }

            // Encode and save the new, updated list back to the site file
            $site->update([
                'scheduled' => Yaml::encode($updatedList),
            ]);

            return 'Scheduled pages processed. Published ' . $publishedCount . ' pages.';
        }
    ]
];