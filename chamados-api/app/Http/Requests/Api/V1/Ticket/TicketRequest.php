<?php

namespace App\Http\Requests\Api\V1\Ticket;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

abstract class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string'],
            'prioridade' => ['required', 'in:'.implode(',', Ticket::PRIORIDADES)],
            'status' => ['required', 'in:'.implode(',', Ticket::STATUSES)],
            'responsavel_id' => ['nullable', 'exists:responsaveis,id'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'atribuicao_automatica' => ['sometimes', 'boolean'],
        ];
    }

    /** Dados para a tabela tickets: fora o flag de atribuicao e tag_ids (vai pro pivot). */
    public function dadosParaPersistencia(): array
    {
        return collect($this->validated())
            ->except(['atribuicao_automatica', 'tag_ids'])
            ->all();
    }
}