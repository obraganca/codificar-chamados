<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ordem importa: usuarios -> responsaveis (ligados por e-mail) ->
        // categorias/tags -> chamados.
        $this->call([
            UserSeeder::class,
            ResponsavelSeeder::class,
            CategoriaSeeder::class,
            TicketSeeder::class,
        ]);
    }
}