<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\User;
use App\Notifications\NewTicketMessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendNewMessageNotification implements ShouldQueue
{
    public function handle(MessageSent $event): void
    {
        $mensagem = $event->message->loadMissing(['user', 'ticket.responsavel']);

        $destinatarios = $mensagem->ticket->participantes()
            ->reject(fn (User $u) => $u->id === $mensagem->user_id);

        Notification::send($destinatarios, new NewTicketMessageNotification($mensagem));
    }
}