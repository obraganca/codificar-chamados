<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Perfil de atendente ligado a este login (1:1, opcional). */
    public function responsavel(): HasOne
    {
        return $this->hasOne(Responsavel::class);
    }

    /** Chamados que este usuario abriu. */
    public function chamadosSolicitados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'solicitante_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}