<?php

namespace Database\Factories;

use App\Models\Responsavel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponsavelFactory extends Factory
{
    protected $model = Responsavel::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
    public function comUsuario(): static
    {
        return $this->state(fn () => ['user_id' => User::factory()]);
    }

}
