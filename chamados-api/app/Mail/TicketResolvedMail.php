<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketResolvedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Chamado finalizado #' . $this->ticket->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chamados.resolved',
            with: [
                'chamado' => $this->ticket,
                'usuario' => $this->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}