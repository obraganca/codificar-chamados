<?php

namespace App\Events;

use App\Http\Resources\TicketResource;
use App\Models\Categoria;
use App\Models\Responsavel;
use App\Models\Ticket;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Disparado pelo TicketObserver quando um campo "notificavel" muda.
 * Dois consumidores:
 *  1. o broadcast (atualiza ao vivo a tela de quem esta com o chamado aberto);
 *  2. o listener SendTicketUpdatedNotifications (notifica os participantes).
 */
class TicketUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, array{de: mixed, para: mixed}>  $mudancas
     * @param  int|null  $atorId  quem alterou (nao recebe a propria notificacao)
     */
    public function __construct(
        public Ticket $ticket,
        public array $mudancas,
        public ?int $atorId = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tickets.chat.{$this->ticket->id}")];
    }

    public function broadcastAs(): string
    {
        return 'TicketUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket' => (new TicketResource(
                $this->ticket->load(['responsavel', 'categoria'])
            ))->resolve(),
        ];
    }

    /**
     * Diff em texto legivel: ids viram nomes e enums viram rotulos.
     *
     * @return array<int, array{campo: string, de: ?string, para: ?string}>
     */
    public function resumo(): array
    {
        $responsaveis = Responsavel::whereIn('id', $this->idsDe('responsavel_id'))->pluck('nome', 'id');
        $categorias = Categoria::whereIn('id', $this->idsDe('categoria_id'))->pluck('nome', 'id');

        $formatar = fn (string $campo, mixed $valor): ?string => match ($campo) {
            'status' => Ticket::STATUS_LABELS[$valor] ?? $valor,
            'prioridade' => Ticket::PRIORIDADE_LABELS[$valor] ?? $valor,
            'responsavel_id' => $valor ? ($responsaveis[$valor] ?? "#{$valor}") : 'Sem responsável',
            'categoria_id' => $valor ? ($categorias[$valor] ?? "#{$valor}") : 'Sem categoria',
            'titulo' => Str::limit((string) $valor, 60),
            default => null, // descricao: so informa que mudou
        };

        $rotulos = [
            'titulo' => 'Título',
            'descricao' => 'Descrição',
            'prioridade' => 'Prioridade',
            'status' => 'Status',
            'responsavel_id' => 'Responsável',
            'categoria_id' => 'Categoria',
        ];

        return collect($this->mudancas)
            ->map(fn (array $m, string $campo) => [
                'campo' => $rotulos[$campo] ?? $campo,
                'de' => $formatar($campo, $m['de']),
                'para' => $formatar($campo, $m['para']),
            ])
            ->values()
            ->all();
    }

    /** @return array<int, int> */
    private function idsDe(string $campo): array
    {
        return collect($this->mudancas[$campo] ?? [])->filter()->values()->all();
    }
}