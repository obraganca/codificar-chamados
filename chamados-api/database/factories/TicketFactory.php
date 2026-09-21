<?php

namespace Database\Factories;

use App\Models\Responsavel;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(4),
            'descricao' => $this->faker->paragraph(),
            'prioridade' => $this->faker->randomElement(Ticket::PRIORIDADES),
            'status' => $this->faker->randomElement(Ticket::STATUSES),
            'responsavel_id' => Responsavel::factory(),
            'aberto_em' => now(),
        ];
    }

    public function aberto(): static
    {
        return $this->state(['status' => Ticket::STATUS_ABERTO]);
    }

    public function fechado(): static
    {
        return $this->state(['status' => Ticket::STATUS_FECHADO]);
    }
}
