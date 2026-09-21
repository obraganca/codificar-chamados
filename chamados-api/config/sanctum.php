<?php

use Laravel\Sanctum\Sanctum;

return [
    // Nao usamos autenticacao via cookie de SPA (stateful), somente tokens
    // Bearer, entao este dominio nao precisa ser configurado para credenciais.
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),

    'guard' => ['web'],

    // Tokens expiram em 7 dias (minutos). O usuario simplesmente loga de novo
    // depois disso; evita tokens validos para sempre em caso de vazamento.
    'expiration' => 60 * 24 * 7,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
