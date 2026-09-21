<?php

return [
    'default' => env('CACHE_STORE', 'redis'),
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
        'array' => [
            'driver' => 'array',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', 'chamados_cache'),
];
