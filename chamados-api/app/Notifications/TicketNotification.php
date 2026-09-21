<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Base das notificacoes de chamado. Um unico contrato de payload, usado no
 * canal "database" (sino do front) e no "broadcast" (tempo real via Reverb):
 *
 *   { type, ticket_id, title, message, actor: {id,name}|null, url, ...extras }
 *
 * Canais padrao: database + broadcast. Subclasses acrescentam 'mail' quando faz sentido.
 */
abstract class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract protected function tipo(): string;

    abstract protected function chamadoId(): int;

    abstract protected function titulo(): string;

    abstract protected function mensagem(): string;

    protected function autor(): ?User
    {
        return null;
    }

    protected function extras(): array
    {
        return [];
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    // O Laravel sobrescreve "type" do broadcast com o nome da classe; isto mantem o id curto.
    public function broadcastType(): string
    {
        return $this->tipo();
    }

    protected function payload(): array
    {
        return [
            'type' => $this->tipo(),
            'ticket_id' => $this->chamadoId(),
            'title' => $this->titulo(),
            'message' => $this->mensagem(),
            'actor' => $this->autor()
                ? ['id' => $this->autor()->id, 'name' => $this->autor()->name]
                : null,
            'url' => '/chamados/'.$this->chamadoId(),
            ...$this->extras(),
        ];
    }
}