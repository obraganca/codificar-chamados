<?php

namespace Tests\Feature;

use App\Models\Responsavel;
use App\Models\Ticket;
use App\Services\TicketAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_escolhe_o_responsavel_com_menos_chamados_em_aberto(): void
    {
        $sobrecarregado = Responsavel::factory()->create();
        $livre = Responsavel::factory()->create();

        Ticket::factory()->count(3)->aberto()->create(['responsavel_id' => $sobrecarregado->id]);
        Ticket::factory()->count(1)->aberto()->create(['responsavel_id' => $livre->id]);

        $escolhido = (new TicketAssignmentService())->escolherResponsavelComMenosChamados();

        $this->assertTrue($escolhido->is($livre));
    }

    public function test_chamados_fechados_nao_contam_para_a_distribuicao(): void
    {
        $responsavelComHistorico = Responsavel::factory()->create();
        $responsavelSemHistorico = Responsavel::factory()->create();

        // Muitos chamados fechados nao devem pesar contra o responsavel.
        Ticket::factory()->count(10)->fechado()->create(['responsavel_id' => $responsavelComHistorico->id]);
        Ticket::factory()->count(1)->aberto()->create(['responsavel_id' => $responsavelSemHistorico->id]);

        $escolhido = (new TicketAssignmentService())->escolherResponsavelComMenosChamados();

        $this->assertTrue($escolhido->is($responsavelComHistorico));
    }

    public function test_em_caso_de_empate_escolhe_o_responsavel_de_menor_id(): void
    {
        $primeiro = Responsavel::factory()->create();
        $segundo = Responsavel::factory()->create();

        $escolhido = (new TicketAssignmentService())->escolherResponsavelComMenosChamados();

        $this->assertTrue($escolhido->is($primeiro));
    }
}
