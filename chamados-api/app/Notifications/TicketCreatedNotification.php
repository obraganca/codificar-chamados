<?php

namespace App\Notifications;

use App\Mail\TicketCreatedMail;
use App\Models\Ticket;
use App\Models\User;

class TicketCreatedNotification extends TicketNotification
{
    public function __construct(public Ticket $ticket, public ?User $ator = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): TicketCreatedMail
    {
        return (new TicketCreatedMail($this->ticket, $notifiable))->to($notifiable->email);
    }

    protected function tipo(): string
    {
        return 'ticket_created';
    }

    protected function chamadoId(): int
    {
        return $this->ticket->id;
    }

    protected function titulo(): string
    {
        return 'Novo chamado criado';
    }

    protected function mensagem(): string
    {
        return "O chamado #{$this->ticket->id} ({$this->ticket->titulo}) foi criado.";
    }

    protected function autor(): ?User
    {
        return $this->ator;
    }
}