<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable/disable Laravel Unleash
    |--------------------------------------------------------------------------
    |
    | Enable/disable switch for the Laravel Unleash wrapper
    |
    */

    'enabled' => env('UNLEASH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Unleash URL
    |--------------------------------------------------------------------------
    |
    | This should be the base URL to your Unleash instance.
    | Do not include /api or anything else
    |
    */

    'url' => env('UNLEASH_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | The name of your application which is passed through to your Unleash
    | instance for identification purposes.
    |
    */

    'application_name' => env('UNLEASH_APPLICATION_NAME', env('app.name')),

    /*
    |--------------------------------------------------------------------------
    | Instance ID
    |--------------------------------------------------------------------------
    |
    | The unique ID of your instance which is passed through to your Unleash
    | instance for identification purposes.
    |
    */

    'instance_id' => env('UNLEASH_INSTANCE_ID', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Specify how long (in seconds) response is waited until considered failed
    */

    'timeout' => 10,

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Unleash cache settings. This will cache any API object responses for the
    | duration of the TTL.
    |
    | Unleash documentation has a recommended "polling" timeout of 15 seconds,
    | so you can mimic that here using the TTL value.
    */

    'cache' => [
        'enabled' => false,
        'ttl' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoint Protection
    |--------------------------------------------------------------------------
    |
    | If the request to your Unleash instance fails, use a cached
    | version of the last successful result.
    |
    */

    'protection' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit breaker
    |--------------------------------------------------------------------------
    |
    | If the request to your Unleash instance fails consistently, use a 
    | circuit breaker and avoid redundant requests
    |
    | For more details see:
    | https://github.com/francescomalatesta/laravel-circuit-breaker
    |
    */

    'circuit_breaker' => [
        'enabled' => env('UNLEASH_CIRCUIT_BREAKER_ENABLED', true),
        'attempts_threshold' => 3,
        'attempts_ttl' => 60,
        'failure_ttl' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Strategies
    |--------------------------------------------------------------------------
    |
    | Mapping of strategies used to guard features on Unleash. The default
    | strategies are already mapped below.
    |
    */
    'strategies' => [
        'applicationHostname'   => \JWebb\Unleash\Strategies\ApplicationHostnameStrategy::class,
        'default'               => \JWebb\Unleash\Strategies\DefaultStrategy::class,
        'remoteAddress'         => \JWebb\Unleash\Strategies\RemoteAddressStrategy::class,
        'userWithId'           => \JWebb\Unleash\Strategies\UserWithIdStrategy::class,
    ],
];
