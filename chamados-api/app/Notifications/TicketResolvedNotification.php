<?php

namespace App\Notifications;

use App\Mail\TicketResolvedMail;
use App\Models\Ticket;
use App\Models\User;

/** Enviada a quem abriu o chamado quando ele e marcado como resolvido. */
class TicketResolvedNotification extends TicketNotification
{
    public function __construct(public Ticket $ticket, public ?User $ator = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): TicketResolvedMail
    {
        return (new TicketResolvedMail($this->ticket, $notifiable))->to($notifiable->email);
    }

    protected function tipo(): string
    {
        return 'ticket_resolved';
    }

    protected function chamadoId(): int
    {
        return $this->ticket->id;
    }

    protected function titulo(): string
    {
        return 'Chamado resolvido';
    }

    protected function mensagem(): string
    {
        return "O chamado #{$this->ticket->id} ({$this->ticket->titulo}) foi resolvido.";
    }

    protected function autor(): ?User
    {
        return $this->ator;
    }
}