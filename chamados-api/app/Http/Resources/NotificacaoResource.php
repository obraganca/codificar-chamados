<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Illuminate\Notifications\DatabaseNotification
 */
class NotificacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->data['type'] ?? null,
            'titulo' => $this->data['title'] ?? '',
            'mensagem' => $this->data['message'] ?? '',
            'ticket_id' => $this->data['ticket_id'] ?? null,
            'autor' => $this->data['actor'] ?? null,
            'mudancas' => $this->data['changes'] ?? null,
            'lida_em' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}