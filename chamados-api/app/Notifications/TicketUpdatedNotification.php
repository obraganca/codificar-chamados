<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;

class TicketUpdatedNotification extends TicketNotification
{
    /** @param array<int, array{campo: string, de: ?string, para: ?string}> $resumo ver TicketUpdated::resumo() */
    public function __construct(
        public Ticket $ticket,
        public array $resumo,
        public ?User $ator = null,
    ) {
    }

    protected function tipo(): string
    {
        return 'ticket_updated';
    }

    protected function chamadoId(): int
    {
        return $this->ticket->id;
    }

    protected function titulo(): string
    {
        return "Chamado #{$this->ticket->id} atualizado";
    }

    protected function mensagem(): string
    {
        $quem = $this->ator?->name ?? 'Alguém';

        $detalhes = collect($this->resumo)->map(function (array $m) {
            return $m['de'] !== null || $m['para'] !== null
                ? "{$m['campo']}: {$m['de']} → {$m['para']}"
                : "{$m['campo']} alterado";
        })->implode('; ');

        return "{$quem} alterou o chamado. {$detalhes}";
    }

    protected function autor(): ?User
    {
        return $this->ator;
    }

    protected function extras(): array
    {
        return ['changes' => $this->resumo];
    }
}