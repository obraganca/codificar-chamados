<?php

namespace Database\Seeders;

use App\Models\Responsavel;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResponsavelSeeder extends Seeder
{
    public function run(): void
    {
        $responsaveis = [
            ['nome' => 'Ana Souza', 'email' => 'ana.souza@codificar.com'],
            ['nome' => 'Bruno Lima', 'email' => 'bruno.lima@codificar.com'],
            ['nome' => 'Carla Mendes', 'email' => 'carla.mendes@codificar.com'],
        ];

        foreach ($responsaveis as $responsavel) {
            Responsavel::updateOrCreate(
                ['email' => $responsavel['email']],
                [
                    ...$responsavel,
                    'user_id' => User::where('email', $responsavel['email'])->value('id'),
                ]
            );
        }
    }
}