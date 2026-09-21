<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Responsavel
 */
class ResponsavelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            // So aparece quando a query anotou a contagem via withCount(); evita
            // um N+1 acidental caso o endpoint nao precise da carga de trabalho.
            'chamados_abertos_count' => $this->when(
                $this->chamados_abertos_count !== null,
                fn () => (int) $this->chamados_abertos_count
            ),
        ];
    }
}
