<?php

namespace App\Services;

use App\Models\Responsavel;
use App\Models\Ticket;
use Closure;
use Illuminate\Support\Facades\Cache;

class TicketAssignmentService
{
    private const LOCK_KEY = 'ticket-auto-assignment-lock';
    private const LOCK_TTL_SECONDS = 10;   // vida maxima do lock se alguem travar
    private const LOCK_WAIT_SECONDS = 5;   // quanto uma requisicao espera na fila

    /**
     * Responsavel com menos chamados em aberto (empate: menor id).
     *
     * @param  int|null  $ignorarChamadoId  nao conta este chamado (ao reatribuir um chamado ja aberto)
     */
    public function escolherResponsavelComMenosChamados(?int $ignorarChamadoId = null): ?Responsavel
    {
        return Responsavel::query()
            ->withCount([
                'chamados as chamados_abertos_count' => function ($query) use ($ignorarChamadoId) {
                    $query->whereIn('status', Ticket::STATUS_ABERTOS)
                        ->when($ignorarChamadoId, fn ($q, $id) => $q->where('tickets.id', '!=', $id));
                },
            ])
            ->orderBy('chamados_abertos_count')
            ->orderBy('id')
            ->first();
    }

    /**
     * Executa $persistir (criar/atualizar o chamado) recebendo o responsavel
     * escolhido. O lock so e solto DEPOIS de gravar; antes era solto logo apos
     * a leitura e nao protegia nada.
     */
    public function comAtribuicaoAutomatica(bool $automatica, Closure $persistir, ?int $ignorarChamadoId = null): mixed
    {
        if (! $automatica) {
            return $persistir(null);
        }

        return Cache::lock(self::LOCK_KEY, self::LOCK_TTL_SECONDS)
            ->block(self::LOCK_WAIT_SECONDS, function () use ($persistir, $ignorarChamadoId) {
                return $persistir($this->escolherResponsavelComMenosChamados($ignorarChamadoId));
            });
    }
}