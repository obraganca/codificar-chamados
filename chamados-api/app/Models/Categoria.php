<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nome', 'cor'];

    public function chamados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'categoria_id');
    }
}