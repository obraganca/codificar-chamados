<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotificacaoController;
use App\Http\Controllers\Api\V1\ResponsavelController;
use App\Http\Controllers\Api\V1\TicketChatController;
use App\Http\Controllers\Api\V1\TicketController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Versionada desde o inicio (v1): permite evoluir o contrato no futuro sem
// quebrar clientes antigos, sem custo nenhum hoje.
Route::prefix('v1')->group(function () {
    Route::post('/auth/registrar', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/responsaveis', [ResponsavelController::class, 'index']);
        Route::get('/categorias', [CatalogoController::class, 'categorias']);
        Route::get('/tags', [CatalogoController::class, 'tags']);



        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/responsaveis', [ResponsavelController::class, 'index']);

        Route::apiResource('chamados', TicketController::class)
            ->parameters(['chamados' => 'chamado'])
            ->except(['destroy']);

        Route::get('/chamados/{chamado}/messages', [TicketChatController::class, 'listMessages']);
        Route::post('/chamados/{chamado}/messages', [TicketChatController::class, 'sendMessage']);

        Route::get('/notificacoes', [NotificacaoController::class, 'index']);
        Route::post('/notificacoes/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasComoLidas']);
        Route::post('/notificacoes/{id}/lida', [NotificacaoController::class, 'marcarComoLida']);

        Broadcast::routes(['middleware' => ['auth:sanctum']]);
    });
});