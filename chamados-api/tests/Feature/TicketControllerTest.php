<?php

namespace Tests\Feature;

use App\Models\Responsavel;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_sem_token_e_bloqueado(): void
    {
        $this->getJson('/api/v1/chamados')->assertStatus(401);
    }

    public function test_lista_chamados_paginada(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Ticket::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/chamados');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_filtra_chamados_por_status(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Ticket::factory()->aberto()->create(['titulo' => 'Chamado aberto']);
        Ticket::factory()->fechado()->create(['titulo' => 'Chamado fechado']);

        $response = $this->getJson('/api/v1/chamados?status=aberto');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Chamado aberto');
    }

    public function test_cria_chamado_com_responsavel_manual(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $responsavel = Responsavel::factory()->create();

        $response = $this->postJson('/api/v1/chamados', [
            'titulo' => 'Impressora com defeito',
            'descricao' => 'Nao imprime em cores.',
            'prioridade' => 'media',
            'status' => 'aberto',
            'responsavel_id' => $responsavel->id,
        ]);

        $response->assertCreated()->assertJsonPath('data.responsavel_id', $responsavel->id);
        $this->assertDatabaseHas('tickets', [
            'titulo' => 'Impressora com defeito',
            'responsavel_id' => $responsavel->id,
        ]);
    }

    public function test_cria_chamado_com_atribuicao_automatica(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $livre = Responsavel::factory()->create();
        $ocupado = Responsavel::factory()->create();
        Ticket::factory()->count(5)->aberto()->create(['responsavel_id' => $ocupado->id]);

        $response = $this->postJson('/api/v1/chamados', [
            'titulo' => 'Cadeira quebrada',
            'descricao' => 'Apoio de braco solto.',
            'prioridade' => 'baixa',
            'status' => 'aberto',
            'atribuicao_automatica' => true,
        ]);

        $response->assertCreated()->assertJsonPath('data.responsavel_id', $livre->id);
    }

    public function test_valida_campos_obrigatorios(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/chamados', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['titulo', 'descricao', 'prioridade', 'status']);
    }

    public function test_atualiza_chamado(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $chamado = Ticket::factory()->create(['status' => 'aberto']);

        $response = $this->putJson("/api/v1/chamados/{$chamado->id}", [
            'titulo' => $chamado->titulo,
            'descricao' => $chamado->descricao,
            'prioridade' => $chamado->prioridade,
            'status' => 'em_andamento',
        ]);

        $response->assertOk()->assertJsonPath('data.status', 'em_andamento');
    }

    public function test_visualiza_um_chamado(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $chamado = Ticket::factory()->create();

        $this->getJson("/api/v1/chamados/{$chamado->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $chamado->id);
    }
}
