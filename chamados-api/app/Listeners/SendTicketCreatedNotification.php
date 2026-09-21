<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendTicketCreatedNotification implements ShouldQueue
{
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket->loadMissing(['solicitante', 'responsavel.user']);
        $ator = $ticket->solicitante;
        $usuarioDoResponsavel = $ticket->responsavel?->user;

        // Quem recebeu o chamado: "atribuido a voce".
        if ($usuarioDoResponsavel && $usuarioDoResponsavel->isNot($ator)) {
            $usuarioDoResponsavel->notify(new TicketAssignedNotification($ticket, $ator));
        }

        // Admins: "novo chamado", exceto quem criou e o responsavel (ja avisado).
        $admins = User::where('role', 'admin')->get()
            ->reject(fn (User $u) => $u->is($ator) || $u->is($usuarioDoResponsavel));

        Notification::send($admins, new TicketCreatedNotification($ticket, $ator));
    }
}