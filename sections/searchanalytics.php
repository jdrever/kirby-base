<?php

/**
 * Search Analytics Panel Section
 *
 * Displays top search terms and keywords from the search log.
 */

return [
    'props' => [
        'headline' => function ($headline = 'Search Analytics') {
            return $headline;
        },
        'limit' => function ($limit = 20) {
            return $limit;
        }
    ],
    'computed' => [
        'topTerms' => function () {
            $searchLog = $this->kirby()->site()->children()->template('search_log')->first();
            if (!$searchLog) {
                return [];
            }

            $logEntries = $searchLog->children()->template('search_log_item');
            $termCounts = [];

            foreach ($logEntries as $entry) {
                $query = strtolower(trim($entry->searchQuery()->value() ?? ''));
                if ($query !== '') {
                    $termCounts[$query] = ($termCounts[$query] ?? 0) + 1;
                }
            }

            arsort($termCounts);
            $topTerms = array_slice($termCounts, 0, $this->limit, true);

            $result = [];
            foreach ($topTerms as $term => $count) {
                $result[] = ['term' => $term, 'count' => $count];
            }

            return $result;
        },

        'topKeywords' => function () {
            $stopWords = [
                'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
                'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
                'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
                'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need',
                'this', 'that', 'these', 'those', 'it', 'its', 'i', 'me', 'my', 'we',
                'our', 'you', 'your', 'he', 'she', 'they', 'them', 'their', 'what',
                'which', 'who', 'whom', 'how', 'when', 'where', 'why', 'all', 'any',
                'both', 'each', 'more', 'most', 'other', 'some', 'such', 'no', 'not',
                'only', 'same', 'so', 'than', 'too', 'very', 'just', 'also', 'now'
            ];

            $searchLog = $this->kirby()->site()->children()->template('search_log')->first();
            if (!$searchLog) {
                return [];
            }

            $logEntries = $searchLog->children()->template('search_log_item');
            $keywordCounts = [];

            foreach ($logEntries as $entry) {
                $query = strtolower(trim($entry->searchQuery()->value() ?? ''));
                if ($query === '') {
                    continue;
                }

                // Split query into words, filter stop words and short words
                $words = preg_split('/\s+/', $query);
                foreach ($words as $word) {
                    $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
                    if (strlen($word) >= 2 && !in_array($word, $stopWords, true)) {
                        $keywordCounts[$word] = ($keywordCounts[$word] ?? 0) + 1;
                    }
                }
            }

            arsort($keywordCounts);
            $topKeywords = array_slice($keywordCounts, 0, $this->limit, true);

            $result = [];
            foreach ($topKeywords as $keyword => $count) {
                $result[] = ['keyword' => $keyword, 'count' => $count];
            }

            return $result;
        },

        'summary' => function () {
            $searchLog = $this->kirby()->site()->children()->template('search_log')->first();
            if (!$searchLog) {
                return [
                    'totalSearches' => 0,
                    'uniqueTerms' => 0,
                    'dateRange' => ['from' => null, 'to' => null]
                ];
            }

            $logEntries = $searchLog->children()->template('search_log_item')->sortBy('searchDate', 'asc');
            $totalSearches = $logEntries->count();
            $uniqueTerms = [];
            $firstDate = null;
            $lastDate = null;

            foreach ($logEntries as $entry) {
                $query = strtolower(trim($entry->searchQuery()->value() ?? ''));
                if ($query !== '') {
                    $uniqueTerms[$query] = true;
                }
                $date = $entry->searchDate()->value();
                if ($firstDate === null) {
                    $firstDate = $date;
                }
                $lastDate = $date;
            }

            return [
                'totalSearches' => $totalSearches,
                'uniqueTerms' => count($uniqueTerms),
                'dateRange' => ['from' => $firstDate, 'to' => $lastDate]
            ];
        }
    ],
];
