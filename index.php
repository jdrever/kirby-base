<?php /** @noinspection PhpUnhandledExceptionInspection */

use BSBI\WebBase\helpers\ContentIndexDefinition;
use BSBI\WebBase\helpers\ContentIndexRegistry;
use BSBI\WebBase\helpers\FilteredFilesHelper;
use BSBI\WebBase\helpers\FilteredPagesHelper;
use BSBI\WebBase\helpers\FormSubmissionIndexDefinition;
use BSBI\WebBase\helpers\SearchIndexHelper;
use Kirby\Cms\App as Kirby;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Tpl;

$pluginConfig = [
    'blueprints' => require __DIR__ . '/blueprints.php',
    'snippets' => require __DIR__ . '/snippets.php',
    'hooks' => require __DIR__ . '/hooks.php',
    'routes' => require __DIR__ . '/routes.php',
    'templates' => [
        'file_link' => __DIR__ . '/templates/file_link.php',
        'page_link' => __DIR__ . '/templates/page_link.php',
        'emails/form-notification.html' => __DIR__ . '/templates/emails/form-notification.html.php',
        'emails/form-notification.text' => __DIR__ . '/templates/emails/form-notification.text.php',
        'search_log' => __DIR__ . '/templates/search_log.php',
        'search_log_item' => __DIR__ . '/templates/search_log_item.php',
    ],
    'controllers' => [
        'image_bank' =>  require __DIR__ . '/controllers/image_bank.php',
        'file_link' =>  require __DIR__ . '/controllers/file_link.php',
        'page_link' =>  require __DIR__ . '/controllers/page_link.php',
    ],
    'collections' => [
        'formSubmissions' => require __DIR__ . '/collections/formSubmissions.php',
    ],
    'sections' => [
        'formsubmissionexport' => require __DIR__ . '/sections/formsubmissionexport.php',
        'formsubmissionsindex' => require __DIR__ . '/sections/formsubmissionsindex.php',
        'quicklinks' => require __DIR__ . '/sections/quicklinks.php',
        'searchanalytics' => require __DIR__ . '/sections/searchanalytics.php',
        'searchindexstats' => require __DIR__ . '/sections/searchindexstats.php',
        'contentindexstats' => require __DIR__ . '/sections/contentindexstats.php',
        'translatedpages' => require __DIR__ . '/sections/translatedpages.php',
        'filteredpages'   => require __DIR__ . '/sections/filteredpages.php',
        'filteredfiles'   => require __DIR__ . '/sections/filteredfiles.php',
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'filtered-files/options',
                'method'  => 'GET',
                'action'  => function (): array {
                    $filterDefs = json_decode(get('filters', '{}'), true) ?? [];
                    $modelId    = (string)get('model_id', '');
                    return FilteredFilesHelper::getOptions($filterDefs, $modelId);
                },
            ],
            [
                'pattern' => 'filtered-files/results',
                'method'  => 'GET',
                'action'  => function (): array {
                    $modelId    = (string)get('model_id', '');
                    $filterDefs = json_decode(get('filters', '{}'), true) ?? [];
                    $columnDefs = json_decode(get('columns', '[]'), true) ?? [];
                    $active     = json_decode(get('active', '{}'), true) ?? [];
                    $search     = (string)get('search', '');
                    $sortParts  = explode(' ', (string)get('sort', 'filename asc'), 2);
                    $sortField  = $sortParts[0] ?? 'filename';
                    $sortDir    = strtolower($sortParts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                    $page       = max(1, (int)get('page', 1));
                    $pageSize   = max(1, min(200, (int)get('page_size', 25)));

                    return FilteredFilesHelper::getResults(
                        $modelId,
                        $filterDefs,
                        $columnDefs,
                        $active,
                        $search,
                        $sortField,
                        $sortDir,
                        $page,
                        $pageSize
                    );
                },
            ],
            [
                'pattern' => 'filtered-pages/options',
                'method'  => 'GET',
                'action'  => function (): array {
                    $filterDefs = json_decode(get('filters', '{}'), true) ?? [];
                    return FilteredPagesHelper::getOptions($filterDefs);
                },
            ],
            [
                'pattern' => 'filtered-pages/results',
                'method'  => 'GET',
                'action'  => function (): array {
                    $modelId    = (string)get('model_id', '');
                    $template   = (string)get('template', '');
                    $filterDefs = json_decode(get('filters', '{}'), true) ?? [];
                    $columnDefs = json_decode(get('columns', '[]'), true) ?? [];
                    $active     = json_decode(get('active', '{}'), true) ?? [];
                    $search     = (string)get('search', '');
                    $sortParts  = explode(' ', (string)get('sort', 'title asc'), 2);
                    $sortField  = $sortParts[0] ?? 'title';
                    $sortDir    = strtolower($sortParts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                    $page       = max(1, (int)get('page', 1));
                    $pageSize   = max(1, min(200, (int)get('page_size', 25)));

                    return FilteredPagesHelper::getResults(
                        $modelId,
                        $template,
                        $filterDefs,
                        $columnDefs,
                        $active,
                        $search,
                        $sortField,
                        $sortDir,
                        $page,
                        $pageSize
                    );
                },
            ],
        ],
    ],
];

// Override panel page search with fast SQLite-backed search when enabled
if (option('search.panelSearch', false)) {
    $pluginConfig['areas'] = [
        'site' => function () {
            return [
                'searches' => [
                    'pages' => [
                        'label' => I18n::translate('pages'),
                        'icon'  => 'page',
                        'query' => function (string|null $query, int $limit, int $page) {
                            if (empty($query)) {
                                return ['results' => [], 'pagination' => null];
                            }

                            try {
                                $searchIndex = new SearchIndexHelper();
                                $offset = ($page - 1) * $limit;
                                $searchResult = $searchIndex->searchAllPages($query, $limit, $offset);

                                $pageIds = $searchResult['results'];
                                $total = $searchResult['total'];

                                if (empty($pageIds)) {
                                    return ['results' => [], 'pagination' => null];
                                }

                                // Load Kirby page objects and filter to listable
                                $pages = pages($pageIds)->filter('isListable', true);

                                $results = $pages->values(
                                    fn ($p) => (new PageItem(page: $p, info: '{{ page.id }}'))->props()
                                );

                                return [
                                    'results'    => $results,
                                    'pagination' => [
                                        'page'   => $page,
                                        'total'  => $total,
                                        'limit'  => $limit,
                                        'pages'  => (int)ceil($total / $limit),
                                        'offset' => $offset,
                                    ]
                                ];
                            } catch (Throwable $e) {
                                error_log('Panel search failed, falling back to default: ' . $e->getMessage());

                                // Fall back to default Kirby panel search
                                return \Kirby\Panel\Controller\Search::pages($query, $limit, $page);
                            }
                        }
                    ]
                ]
            ];
        }
    ];
}

Kirby::plugin('open-foundations/kirby-base', $pluginConfig);

// Register built-in form submissions index
try {
    ContentIndexRegistry::register(new FormSubmissionIndexDefinition());
} catch (Throwable $e) {
    error_log('Failed to register form submissions content index: ' . $e->getMessage());
}

// Register content indexes from site configuration
$contentIndexes = option('contentIndexes', []);
foreach ($contentIndexes as $definition) {
    if ($definition instanceof ContentIndexDefinition) {
        try {
            ContentIndexRegistry::register($definition);
        } catch (Throwable $e) {
            error_log('Failed to register content index "' . $definition->getName() . '": ' . $e->getMessage());
        }
    }
}

if (option('debug') === false) {
// Set a global exception handler
    set_exception_handler(function (Throwable $exception) {

        $pageUrl = $_SERVER['REDIRECT_URL'] ?? '';
        $exceptionAsString = "Message: " . $exception->getMessage() . "\n" .
            "File:" . $exception->getFile() . "'\n" .
            "Line:" . $exception->getLine() . "\n" .
            "Trace:" . $exception->getTraceAsString() . "\n" .
            "Page: " . $pageUrl . "\n";

        error_log($exceptionAsString);

        if (!str_starts_with((string) $_SERVER['HTTP_HOST'], 'localhost')) {
            try {
                $email = [
                    'to' => option('adminEmail'),
                    'from' => option('defaultEmail'),
                    'subject' => 'Website Exception: ' . $exception->getMessage(),
                    'body' => [
                        'html' => "<b>An unhandled exception occurred:</b><br>" .
                            "<b>Message</b>: " . $exception->getMessage() . "<br>" .
                            "<b>File:</b> " . $exception->getFile() . "<br>" .
                            "<b>Line:</b> " . $exception->getLine() . "<br>" .
                            "<b>Trace:</b> " . $exception->getTraceAsString() . "<br>" .
                            "<b>Page:</b> " . $pageUrl,
                    ]
                ];
                kirby()->email($email);
            } catch (Throwable) {
                //continue if an exception occurs when sending the email
            }
        }

        // Render the error page and pass the exception
        $kirby = Kirby::instance();
        $kirby->response()->code(500); // Set HTTP status code to 500
        echo Tpl::load(__DIR__ . '/templates/error-500.php', [
            'userRole' => $kirby->user() ? $kirby->user()->role()->name() : '',
            'exception' => $exceptionAsString,
        ]);
        exit;
    });
}
