<?php

namespace App\Observers;

use App\Events\TicketUpdated;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TicketObserver
{
    public const DASHBOARD_CACHE_KEY = 'dashboard:v1';

    /** Antes do save ainda temos o valor antigo (getOriginal) e o novo (getDirty). */
    public function updating(Ticket $ticket): void
    {
        $ticket->mudancasPendentes = collect($ticket->getDirty())
            ->only(Ticket::CAMPOS_NOTIFICAVEIS)
            ->map(fn ($novo, $campo) => ['de' => $ticket->getOriginal($campo), 'para' => $novo])
            ->all();
    }

    public function updated(Ticket $ticket): void
    {
        if ($ticket->mudancasPendentes !== []) {
            // O ator e capturado agora (requisicao HTTP): na fila nao ha usuario autenticado.
            TicketUpdated::dispatch($ticket, $ticket->mudancasPendentes, Auth::id());
        }

        $ticket->mudancasPendentes = [];
    }

    public function saved(Ticket $ticket): void
    {
        Cache::forget(self::DASHBOARD_CACHE_KEY);
    }

    public function deleted(Ticket $ticket): void
    {
        Cache::forget(self::DASHBOARD_CACHE_KEY);
    }
}