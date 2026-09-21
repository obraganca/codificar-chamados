<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificacaoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificacaoController extends Controller
{
    /** GET /notificacoes?nao_lidas=1 (meta.nao_lidas = contador do sino) */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = $request->boolean('nao_lidas')
            ? $user->unreadNotifications()
            : $user->notifications();

        return NotificacaoResource::collection($query->paginate(15)->withQueryString())
            ->additional(['meta' => ['nao_lidas' => $user->unreadNotifications()->count()]]);
    }

    /** POST /notificacoes/{id}/lida */
    public function marcarComoLida(Request $request, string $id): JsonResponse
    {
        $notificacao = $request->user()->notifications()->findOrFail($id);
        $notificacao->markAsRead();

        return response()->json([
            'data' => (new NotificacaoResource($notificacao->fresh()))->resolve(),
            'meta' => ['nao_lidas' => $request->user()->unreadNotifications()->count()],
        ]);
    }

    /** POST /notificacoes/marcar-todas-lidas */
    public function marcarTodasComoLidas(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['meta' => ['nao_lidas' => 0]]);
    }
}