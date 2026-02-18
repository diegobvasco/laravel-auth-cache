<?php

return [
    'cache' => [
        'enabled' => (bool) env('AUTH_CACHE_ENABLED', true),
        'ttl' => (int) env('AUTH_CACHE_TTL', 60),
        'store' => env('AUTH_CACHE_STORE'),
        'prefix' => env('AUTH_CACHE_PREFIX', 'auth'),
    ],
];
