<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin(
    'open-foundations/kirby-base', [
            'blueprints' => require __DIR__ . '/blueprints.php',
            'snippets' => require __DIR__ . '/snippets.php',
            'hooks' => require __DIR__ . '/hooks.php',
            'templates' => [
                'file_link' => __DIR__ . '/templates/file_link.php',
            ]

        ]
);

if (option('debug') === false) {
// Set a global exception handler
    set_exception_handler(function (Throwable $exception) {
        $exceptionAsString = "Message: " . $exception->getMessage() . "\n" .
            "File:" . $exception->getFile() . "'\n" .
            "Line:" . $exception->getLine() . "\n" .
            "Trace:" . $exception->getTraceAsString();

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
                            "<b>Trace:</b> " . $exception->getTraceAsString(),
                    ]
                ];
                kirby()->email($email);
            } catch (Throwable $exception) {
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
