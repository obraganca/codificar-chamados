<?php

namespace App\Models;

use App\Events\TicketCreated;
use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(TicketObserver::class)]
class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    const STATUS_ABERTO = 'aberto';
    const STATUS_EM_ANDAMENTO = 'em_andamento';
    const STATUS_RESOLVIDO = 'resolvido';
    const STATUS_FECHADO = 'fechado';

    const STATUSES = [
        self::STATUS_ABERTO,
        self::STATUS_EM_ANDAMENTO,
        self::STATUS_RESOLVIDO,
        self::STATUS_FECHADO,
    ];

    const STATUS_ABERTOS = [
        self::STATUS_ABERTO,
        self::STATUS_EM_ANDAMENTO,
    ];

    const STATUS_LABELS = [
        self::STATUS_ABERTO => 'Aberto',
        self::STATUS_EM_ANDAMENTO => 'Em andamento',
        self::STATUS_RESOLVIDO => 'Resolvido',
        self::STATUS_FECHADO => 'Fechado',
    ];

    const PRIORIDADES = ['baixa', 'media', 'alta'];

    const PRIORIDADE_LABELS = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
    ];

    /** Campos cuja alteracao gera notificacao ("edicao do chamado"). */
    const CAMPOS_NOTIFICAVEIS = [
        'titulo',
        'descricao',
        'prioridade',
        'status',
        'responsavel_id',
        'categoria_id',
    ];

    protected $dispatchesEvents = ['created' => TicketCreated::class];

    protected $fillable = [
        'titulo',
        'descricao',
        'prioridade',
        'status',
        'responsavel_id',
        'solicitante_id',
        'categoria_id',
        'aberto_em',
    ];

    protected $casts = [
        'aberto_em' => 'datetime',
    ];

    /**
     * Diff (de/para) calculado no "updating" pelo TicketObserver e lido no
     * "updated". Precisa ser propriedade DECLARADA: num Model, atribuir a uma
     * propriedade inexistente viraria um atributo (coluna).
     */
    public array $mudancasPendentes = [];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class, 'responsavel_id');
    }

    /** Usuario que abriu o chamado. */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tag')->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function isAberto(): bool
    {
        return in_array($this->status, self::STATUS_ABERTOS, true);
    }

    /** alta > media > baixa. CASE em vez de FIELD() (FIELD so existe no MySQL). */
    public function scopeOrdenadoPorPrioridade(Builder $query): Builder
    {
        return $query->orderByRaw(
            "CASE prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END"
        );
    }

    /**
     * Usuarios diretamente envolvidos: quem abriu + login do responsavel atual.
     * E quem recebe notificacoes e participa do chat.
     *
     * @return Collection<int, User>
     */
    public function participantes(): Collection
    {
        $ids = collect([
            $this->solicitante_id,
            $this->responsavel?->user_id,
        ])->filter()->unique()->values();

        return User::whereIn('id', $ids)->get();
    }

    /** Quem pode ler/escrever no chat: participantes e admins. */
    public function podeSerAcessadoPor(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $this->solicitante_id === (int) $user->id
            || ($this->responsavel !== null && (int) $this->responsavel->user_id === (int) $user->id);
    }
}