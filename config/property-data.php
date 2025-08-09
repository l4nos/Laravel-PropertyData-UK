<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Property Data Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Laravel
    | Property Data package to connect to the Property Data API.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Property Data API connection.
    |
    */
    'api' => [
        'base_url' => env('PROPERTY_DATA_API_URL', 'https://api.propertydata.co.uk'),
        'key' => env('PROPERTY_DATA_API_KEY', ''),
        'timeout' => env('PROPERTY_DATA_API_TIMEOUT', 30),

        /*
        |--------------------------------------------------------------------------
        | Retry Configuration
        |--------------------------------------------------------------------------
        |
        | Configure automatic retry behavior for transient failures.
        |
        */
        'retry' => [
            'enabled' => env('PROPERTY_DATA_RETRY_ENABLED', true),
            'max_attempts' => env('PROPERTY_DATA_RETRY_ATTEMPTS', 3),
            'delay' => env('PROPERTY_DATA_RETRY_DELAY', 1000), // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for the property data package.
    |
    */
    'logging' => [
        'enabled' => env('PROPERTY_DATA_LOGGING_ENABLED', true),
        'channel' => env('PROPERTY_DATA_LOG_CHANNEL', config('logging.default')),
    ],
];
