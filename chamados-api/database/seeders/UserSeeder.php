<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['name' => 'Administrador', 'email' => 'admin@codificar.com', 'role' => 'admin'],
            ['name' => 'Ana Souza', 'email' => 'ana.souza@codificar.com', 'role' => 'agente'],
            ['name' => 'Bruno Lima', 'email' => 'bruno.lima@codificar.com', 'role' => 'agente'],
            ['name' => 'Carla Mendes', 'email' => 'carla.mendes@codificar.com', 'role' => 'agente'],
            ['name' => 'Maria Solicitante', 'email' => 'maria@codificar.com', 'role' => 'usuario'],
        ];

        foreach ($usuarios as $dados) {
            User::updateOrCreate(
                ['email' => $dados['email']],
                [
                    ...$dados,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}