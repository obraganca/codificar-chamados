<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_consegue_fazer_login_e_recebe_um_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_login_falha_com_senha_incorreta(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_usuario_consegue_se_registrar_e_recebe_um_token(): void
    {
        $response = $this->postJson('/api/v1/auth/registrar', [
            'name' => 'Novo Usuario',
            'email' => 'novo@codificar.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'novo@codificar.com']);
    }

    public function test_rota_protegida_exige_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_usuario_autenticado_consegue_ver_seus_dados_e_fazer_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('user.id', $user->id);

        $this->postJson('/api/v1/auth/logout')->assertOk();
    }
}
