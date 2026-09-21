<?php

namespace App\Events;

use App\Http\Resources\TicketMessageResource;
use App\Models\TicketMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Disparado automaticamente quando um TicketMessage e criado ($dispatchesEvents). */
class MessageSent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public TicketMessage $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tickets.chat.{$this->message->ticket_id}")];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => (new TicketMessageResource(
                $this->message->loadMissing('user:id,name')
            ))->resolve(),
        ];
    }
}