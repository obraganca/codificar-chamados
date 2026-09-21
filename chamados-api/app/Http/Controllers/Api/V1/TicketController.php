<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\StoreTicketRequest;
use App\Http\Requests\Api\V1\Ticket\TicketRequest;
use App\Http\Requests\Api\V1\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Responsavel;
use App\Models\Ticket;
use App\Services\TicketAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    private const RELACOES = ['responsavel', 'solicitante', 'categoria', 'tags'];

    public function __construct(
        private readonly TicketAssignmentService $assignmentService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $chamados = Ticket::query()
            ->with(self::RELACOES)
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('prioridade')->toString(), fn ($q, $prioridade) => $q->where('prioridade', $prioridade))
            ->when($request->string('responsavel_id')->toString(), fn ($q, $id) => $q->where('responsavel_id', $id))
            ->when($request->string('categoria_id')->toString(), fn ($q, $id) => $q->where('categoria_id', $id))
            ->when($request->string('busca')->toString(), function ($q, $busca) {
                $q->where(function ($q) use ($busca) {
                    $q->where('titulo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%");
                });
            })
            ->ordenadoPorPrioridade()
            ->orderByDesc('aberto_em')
            ->paginate(15)
            ->withQueryString();

        return TicketResource::collection($chamados);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $dados = $request->dadosParaPersistencia();

        // Escolher o responsavel e gravar o chamado acontecem DENTRO do mesmo lock.
        $chamado = $this->assignmentService->comAtribuicaoAutomatica(
            $request->boolean('atribuicao_automatica'),
            fn (?Responsavel $automatico) => Ticket::create([
                ...$dados,
                ...$this->responsavelAutomatico($request, $automatico),
                'solicitante_id' => $request->user()->id,
                'aberto_em' => now(),
            ]),
        );

        $chamado->tags()->sync($request->input('tag_ids', []));

        return (new TicketResource($chamado->load(self::RELACOES)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ticket $chamado): TicketResource
    {
        return new TicketResource($chamado->load(self::RELACOES));
    }

    public function update(UpdateTicketRequest $request, Ticket $chamado): TicketResource
    {
        $dados = $request->dadosParaPersistencia();

        // O TicketObserver detecta o que mudou e dispara TicketUpdated
        // (broadcast + notificacoes). Nao ha mais Redis::publish manual aqui.
        $this->assignmentService->comAtribuicaoAutomatica(
            $request->boolean('atribuicao_automatica'),
            fn (?Responsavel $automatico) => $chamado->update([
                ...$dados,
                ...$this->responsavelAutomatico($request, $automatico),
            ]),
            ignorarChamadoId: $chamado->id,
        );

        // Omitir tag_ids mantem as tags atuais; enviar [] remove todas.
        if ($request->has('tag_ids')) {
            $chamado->tags()->sync($request->input('tag_ids', []));
        }

        return new TicketResource($chamado->load(self::RELACOES));
    }

    /** Com atribuicao automatica, o responsavel escolhido vence qualquer responsavel_id enviado. */
    private function responsavelAutomatico(TicketRequest $request, ?Responsavel $automatico): array
    {
        return $request->boolean('atribuicao_automatica')
            ? ['responsavel_id' => $automatico?->id]
            : [];
    }
}