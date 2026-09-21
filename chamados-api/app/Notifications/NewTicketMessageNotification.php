<?php

namespace App\Notifications;

use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Str;

/** Nova mensagem no chat. Sem e-mail: chat gera volume demais. */
class NewTicketMessageNotification extends TicketNotification
{
    public function __construct(public TicketMessage $message)
    {
    }

    protected function tipo(): string
    {
        return 'ticket_message';
    }

    protected function chamadoId(): int
    {
        return $this->message->ticket_id;
    }

    protected function titulo(): string
    {
        return "Nova mensagem no chamado #{$this->message->ticket_id}";
    }

    protected function mensagem(): string
    {
        return "{$this->message->user->name}: ".Str::limit($this->message->message, 120);
    }

    protected function autor(): ?User
    {
        return $this->message->user;
    }
}