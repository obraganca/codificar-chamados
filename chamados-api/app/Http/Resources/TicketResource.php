<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ticket
 */
class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'prioridade' => $this->prioridade,
            'status' => $this->status,
            'aberto_em' => $this->aberto_em?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'responsavel_id' => $this->responsavel_id,
            'solicitante_id' => $this->solicitante_id,
            'categoria_id' => $this->categoria_id,
            'responsavel' => new ResponsavelResource($this->whenLoaded('responsavel')),
            'solicitante' => $this->whenLoaded('solicitante', fn () => $this->solicitante
                ? ['id' => $this->solicitante->id, 'name' => $this->solicitante->name]
                : null),
            'categoria' => $this->whenLoaded('categoria', fn () => $this->categoria
                ? ['id' => $this->categoria->id, 'nome' => $this->categoria->nome, 'cor' => $this->categoria->cor]
                : null),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags
                ->map(fn ($tag) => ['id' => $tag->id, 'nome' => $tag->nome])
                ->values()),
        ];
    }
}