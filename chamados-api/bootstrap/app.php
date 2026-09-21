<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Registra POST /api/broadcasting/auth protegida por Sanctum (Bearer token),
    // em vez do padrao "web" (que exigiria sessao/cookie, que nao usamos aqui).
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api/v1', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Aplicacao e apenas API: toda excecao vira JSON, nunca uma pagina
        // de erro em HTML, independente do header Accept do cliente.
        $exceptions->shouldRenderJsonWhen(fn () => true);

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'Nao autenticado.',
            ], 401);
        });
    })->create();
