<?php

namespace App\Listeners;

use App\Events\TicketUpdated;
use App\Models\Responsavel;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketResolvedNotification;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTicketUpdatedNotifications implements ShouldQueue
{
    public function handle(TicketUpdated $event): void
    {
        $ticket = $event->ticket->loadMissing(['solicitante', 'responsavel.user']);
        $ator = $event->atorId ? User::find($event->atorId) : null;

        $mudou = fn (string $campo) => array_key_exists($campo, $event->mudancas);

        $novoResponsavel = $ticket->responsavel?->user;

        $responsavelAnterior = null;
        if ($mudou('responsavel_id') && ($idAnterior = $event->mudancas['responsavel_id']['de'])) {
            $responsavelAnterior = Responsavel::with('user')->find($idAnterior)?->user;
        }

        $foiResolvido = ($event->mudancas['status']['para'] ?? null) === Ticket::STATUS_RESOLVIDO;

        $destinatarios = collect([$ticket->solicitante, $novoResponsavel, $responsavelAnterior])
            ->filter()
            ->unique('id')
            ->reject(fn (User $u) => $u->id === $event->atorId);

        $resumo = $event->resumo();

        foreach ($destinatarios as $usuario) {
            $usuario->notify(match (true) {
                $mudou('responsavel_id') && $novoResponsavel?->is($usuario)
                    => new TicketAssignedNotification($ticket, $ator),
                $foiResolvido && $ticket->solicitante?->is($usuario)
                    => new TicketResolvedNotification($ticket, $ator),
                default
                    => new TicketUpdatedNotification($ticket, $resumo, $ator),
            });
        }
    }
}