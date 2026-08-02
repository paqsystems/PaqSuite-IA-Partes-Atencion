<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1AuthAndSystemTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    public function test_login_devuelve_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => ['token', 'user', 'empresas', 'tenancy', 'db', 'firstLogin', 'minutosWeb'],
            ]);
        $this->assertNotEmpty($response->json('resultado.token'));
    }

    public function test_health_responde_sin_auth(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonPath('resultado.serviceName', 'paqsuite-partes-backend');
    }

    public function test_system_status_requiere_autenticacion(): void
    {
        $this->seed();

        $this->getJson('/api/v1/system/status', $this->tenantHeaders())->assertStatus(401);
    }

    public function test_system_status_ok_con_token(): void
    {
        $this->seed();

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());
        $token = $login->json('resultado.token');

        $response = $this->getJson('/api/v1/system/status', array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure(['error', 'respuesta', 'resultado' => ['installationMode', 'appName', 'environment']]);
    }
}
