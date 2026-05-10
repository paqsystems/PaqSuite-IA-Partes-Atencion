<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1AuthAndSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devuelve_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'dev@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => ['token', 'tokenType', 'user'],
            ]);
        $this->assertNotEmpty($response->json('resultado.token'));
    }

    public function test_system_status_requiere_autenticacion(): void
    {
        $this->seed();

        $this->getJson('/api/v1/system/status')->assertStatus(401);
    }

    public function test_system_status_ok_con_token(): void
    {
        $this->seed();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'dev@example.com',
            'password' => 'password',
        ]);
        $token = $login->json('resultado.token');

        $response = $this->getJson('/api/v1/system/status', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('resultado.installationMode', 'MONO')
            ->assertJsonStructure(['error', 'respuesta', 'resultado' => ['installationMode', 'appName', 'environment']]);
    }
}
