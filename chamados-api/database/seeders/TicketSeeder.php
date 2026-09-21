<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Responsavel;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        if (Ticket::exists()) {
            return;
        }

        $responsaveis = Responsavel::all();

        if ($responsaveis->isEmpty()) {
            return;
        }

        $solicitante = User::where('email', 'maria@codificar.com')->first();
        $categorias = Categoria::pluck('id', 'nome');

        $exemplos = [
            ['titulo' => 'Computador nao liga', 'descricao' => 'O computador da sala 2 nao liga desde ontem.', 'prioridade' => 'alta', 'status' => 'aberto', 'categoria' => 'Hardware', 'tags' => ['urgente']],
            ['titulo' => 'Cadeira quebrada', 'descricao' => 'Preciso de uma cadeira nova, a atual esta com o apoio quebrado.', 'prioridade' => 'baixa', 'status' => 'aberto', 'categoria' => 'Mobiliário', 'tags' => ['compra']],
            ['titulo' => 'Impressora sem tinta', 'descricao' => 'A impressora do 3o andar esta sem tinta.', 'prioridade' => 'media', 'status' => 'em_andamento', 'categoria' => 'Hardware', 'tags' => ['impressora']],
            ['titulo' => 'Acesso ao sistema financeiro', 'descricao' => 'Solicito acesso ao sistema financeiro para o novo colaborador.', 'prioridade' => 'media', 'status' => 'resolvido', 'categoria' => 'Acesso e permissões', 'tags' => ['novo colaborador']],
        ];

        // Sem eventos: o seed nao deve gerar notificacoes/broadcast "de mentira".
        Ticket::withoutEvents(function () use ($exemplos, $responsaveis, $solicitante, $categorias) {
            foreach ($exemplos as $i => $exemplo) {
                $responsavel = $responsaveis[$i % $responsaveis->count()];

                $chamado = Ticket::create([
                    'titulo' => $exemplo['titulo'],
                    'descricao' => $exemplo['descricao'],
                    'prioridade' => $exemplo['prioridade'],
                    'status' => $exemplo['status'],
                    'responsavel_id' => $responsavel->id,
                    'solicitante_id' => $solicitante?->id,
                    'categoria_id' => $categorias[$exemplo['categoria']] ?? null,
                    'aberto_em' => now()->subDays(4 - $i),
                ]);

                $chamado->tags()->sync(Tag::whereIn('nome', $exemplo['tags'])->pluck('id'));

                if ($solicitante && $responsavel->user_id) {
                    TicketMessage::create([
                        'ticket_id' => $chamado->id,
                        'user_id' => $solicitante->id,
                        'message' => 'Olá! Alguém consegue me ajudar com isso?',
                    ]);
                    TicketMessage::create([
                        'ticket_id' => $chamado->id,
                        'user_id' => $responsavel->user_id,
                        'message' => 'Oi! Já estou vendo, te retorno por aqui.',
                    ]);
                }
            }
        });
    }
}