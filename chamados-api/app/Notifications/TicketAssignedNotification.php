<?php

namespace App\Notifications;

use App\Mail\TicketAssignedMail;
use App\Models\Ticket;
use App\Models\User;

/** Enviada ao usuario do responsavel quando um chamado passa a ser dele. */
class TicketAssignedNotification extends TicketNotification
{
    public function __construct(public Ticket $ticket, public ?User $ator = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): TicketAssignedMail
    {
        return (new TicketAssignedMail($this->ticket, $notifiable))->to($notifiable->email);
    }

    protected function tipo(): string
    {
        return 'ticket_assigned';
    }

    protected function chamadoId(): int
    {
        return $this->ticket->id;
    }

    protected function titulo(): string
    {
        return 'Chamado atribuído a você';
    }

    protected function mensagem(): string
    {
        return "O chamado #{$this->ticket->id} ({$this->ticket->titulo}) foi atribuído a você.";
    }

    protected function autor(): ?User
    {
        return $this->ator;
    }
}