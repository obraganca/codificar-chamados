<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responsavel extends Model
{
    protected $table = 'responsaveis';
    use HasFactory;

    protected $fillable = [
        'nome',
        'email',
        'user_id',
    ];

    public function chamados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'responsavel_id');
    }

    /** Login (usuario) que atende como este responsavel. Pode ser nulo. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chamadosAbertosCount(): int
    {
        return $this->chamados()->whereIn('status', Ticket::STATUS_ABERTOS)->count();
    }
}