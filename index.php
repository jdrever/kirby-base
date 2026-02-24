<?php /** @noinspection PhpUnhandledExceptionInspection */

use BSBI\WebBase\helpers\ContentIndexDefinition;
use BSBI\WebBase\helpers\ContentIndexRegistry;
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
    'sections' => [
        'formsubmissionexport' => require __DIR__ . '/sections/formsubmissionexport.php',
        'quicklinks' => require __DIR__ . '/sections/quicklinks.php',
        'searchanalytics' => require __DIR__ . '/sections/searchanalytics.php',
        'searchindexstats' => require __DIR__ . '/sections/searchindexstats.php',
        'contentindexstats' => require __DIR__ . '/sections/contentindexstats.php',
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

        if (!str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
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
