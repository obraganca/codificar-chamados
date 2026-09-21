<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponsavelResource;
use App\Models\Responsavel;
use App\Models\Ticket;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResponsavelController extends Controller
{
    /**
     * Lista de responsaveis com a contagem de chamados em aberto de cada um,
     * usada pelo frontend para popular selects e para o painel de carga do
     * dashboard. Nao ha CRUD dedicado (fora do escopo, ver README).
     */
    public function index(): AnonymousResourceCollection
    {
        $responsaveis = Responsavel::query()
            ->withCount([
                'chamados as chamados_abertos_count' => fn ($q) => $q->whereIn('status', Ticket::STATUS_ABERTOS),
            ])
            ->orderBy('nome')
            ->get();

        return ResponsavelResource::collection($responsaveis);
    }
}
