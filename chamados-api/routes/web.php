<?php

use Illuminate\Support\Facades\Route;

// Esta aplicacao e apenas API (ver routes/api.php). O frontend (SPA) vive em
// um projeto separado. Mantemos uma unica rota informativa aqui; saude do
// servico continua disponivel em GET /up (health check padrao do Laravel).
Route::get('/', fn () => response()->json([
    'app' => config('app.name'),
    'message' => 'API de Chamados. Consulte /api/v1.',
]));
