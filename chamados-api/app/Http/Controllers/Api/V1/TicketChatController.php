<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketMessageResource;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketChatController extends Controller
{
    public function listMessages(Request $request, Ticket $chamado): JsonResponse
    {
        $this->garantirAcesso($request, $chamado);

        $mensagens = $chamado->messages()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => TicketMessageResource::collection($mensagens)->resolve()]);
    }

    public function sendMessage(Request $request, Ticket $chamado): JsonResponse
    {
        $this->garantirAcesso($request, $chamado);

        $dados = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // O "created" do model dispara MessageSent, que (1) faz o broadcast via
        // Reverb e (2) aciona, na fila, a notificacao dos outros participantes.
        $mensagem = TicketMessage::create([
            'ticket_id' => $chamado->id,
            'user_id' => $request->user()->id,
            'message' => $dados['message'],
        ])->load('user:id,name');

        return response()->json(['data' => (new TicketMessageResource($mensagem))->resolve()], 201);
    }

    /**
     * Antes comparava $user->id com $ticket->responsavel_id (tabelas diferentes).
     * Agora usa a relacao real: participantes e admins.
     */
    private function garantirAcesso(Request $request, Ticket $chamado): void
    {
        abort_unless($chamado->podeSerAcessadoPor($request->user()), 403, 'Você não participa deste chamado.');
    }
}