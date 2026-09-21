<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nome' => 'Hardware', 'cor' => '#ef4444'],
            ['nome' => 'Software', 'cor' => '#3b82f6'],
            ['nome' => 'Acesso e permissões', 'cor' => '#f59e0b'],
            ['nome' => 'Infraestrutura', 'cor' => '#10b981'],
            ['nome' => 'Mobiliário', 'cor' => '#8b5cf6'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(['nome' => $categoria['nome']], $categoria);
        }

        foreach (['urgente', 'rede', 'impressora', 'novo colaborador', 'compra'] as $nome) {
            Tag::firstOrCreate(['nome' => $nome]);
        }
    }
}