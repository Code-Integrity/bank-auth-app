<?php

return [

    'dsn' => env('SENTRY_LARAVEL_DSN'),


    'before_send' => function (\Sentry\Event $event) {
        $exceptions = $event->getExceptions();

        if (!empty($exceptions)) {
            foreach ($exceptions as &$exception) {
                if (isset($exception['value'])) {

                    $exception['value'] = preg_replace(
                        '/(base64:[A-Za-z0-9+\/=]+|password=[^\s&]+)/i',
                        '[REDACTED_SENSITIVE_DATA]',
                        $exception['value']
                    );
                }
            }
        }

        return $event;
    },
];
