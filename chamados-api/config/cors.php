<?php

// Como frontend e backend agora sao duas aplicacoes/dominios distintos, o
// navegador so libera as chamadas fetch/axios do SPA para a API se o CORS
// estiver configurado corretamente. Usamos autenticacao via Bearer token
// (nao cookie), entao `supports_credentials` fica false e nao precisamos
// lidar com o problema extra de cookies cross-site.
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('FRONTEND_URLS', 'http://localhost:5173'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
