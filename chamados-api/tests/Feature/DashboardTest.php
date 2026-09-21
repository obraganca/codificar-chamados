<?php

namespace Tests\Feature;

use App\Models\Responsavel;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exige_autenticacao(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
    }

    public function test_dashboard_mostra_chamados_agrupados_por_status_e_totais(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $responsavel = Responsavel::factory()->create();
        Ticket::factory()->count(2)->aberto()->create(['responsavel_id' => $responsavel->id]);
        Ticket::factory()->fechado()->create(['responsavel_id' => $responsavel->id]);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertOk()
            ->assertJsonStructure(['colunas', 'responsaveis', 'totais' => ['total', 'em_aberto', 'alta_prioridade', 'sem_responsavel']])
            ->assertJsonPath('totais.total', 3)
            ->assertJsonPath('totais.em_aberto', 2)
            ->assertJsonCount(2, 'colunas.aberto');
    }
}
