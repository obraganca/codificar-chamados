<?php

return [
    'driver' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'connection' => env('SESSION_CONNECTION', 'default'),
    'table' => 'sessions',
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'chamados_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
];
