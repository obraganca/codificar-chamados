<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponsavelResource;
use App\Http\Resources\TicketResource;
use App\Models\Responsavel;
use App\Models\Ticket;
use App\Observers\TicketObserver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /** Rede de seguranca: normalmente o TicketObserver invalida o cache ao gravar um chamado. */
    private const CACHE_TTL_SECONDS = 60;

    public function index(): JsonResponse
    {
        $payload = Cache::remember(
            TicketObserver::DASHBOARD_CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn () => $this->montarPayload()
        );

        return response()->json($payload);
    }

    private function montarPayload(): array
    {
        $chamados = Ticket::with(['responsavel', 'categoria'])
            ->ordenadoPorPrioridade()
            ->orderByDesc('aberto_em')
            ->get();

        $colunas = collect(Ticket::STATUSES)->mapWithKeys(
            fn ($status) => [$status => TicketResource::collection($chamados->where('status', $status)->values())->resolve()]
        );

        $responsaveis = Responsavel::withCount([
            'chamados as chamados_abertos_count' => fn ($q) => $q->whereIn('status', Ticket::STATUS_ABERTOS),
        ])->orderByDesc('chamados_abertos_count')->get();

        $payload = [
            'colunas' => $colunas,
            'responsaveis' => ResponsavelResource::collection($responsaveis)->resolve(),
            'totais' => [
                'total' => $chamados->count(),
                'em_aberto' => $chamados->whereIn('status', Ticket::STATUS_ABERTOS)->count(),
                'alta_prioridade' => $chamados->where('prioridade', 'alta')->whereIn('status', Ticket::STATUS_ABERTOS)->count(),
                'sem_responsavel' => $chamados->whereNull('responsavel_id')->count(),
            ],
        ];

        // Arrays puros (sem Collections/Resources aninhados) para ir ao Redis.
        return json_decode(json_encode($payload), true);
    }
}