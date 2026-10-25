<?php

namespace Tests\Feature\Partes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ApiV1PartesIntegracionMaestrosTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    /** @return array<string, string> */
    private function authHeaders(): array
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());
        $login->assertOk();

        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$login->json('resultado.token'),
        ]);
    }

    public function test_reconciles_clients_and_supports_dry_run(): void
    {
        $headers = $this->authHeaders();
        DB::table('PQ_PARTES_TIPOS_CLIENTE')->insert([
            'code' => 'EMPRESA',
            'descripcion' => 'Empresa',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/integracion/maestros/clientes', [
            'fechaProceso' => '2026-10-24',
            'origen' => 'ERP',
            'items' => [[
                'code' => 'CLI-001',
                'nombre' => 'Acme SA',
                'tipoCliente' => ['code' => 'EMPRESA'],
                'email' => 'contacto@acme.example',
            ]],
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.recurso', 'clientes')
            ->assertJsonPath('resultado.resultados.0.accion', 'alta');
        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', [
            'code' => 'CLI-001',
            'email' => 'contacto@acme.example',
            'activo' => 1,
            'inhabilitado' => 0,
        ]);

        $dryRun = $this->postJson('/api/v1/integracion/maestros/clientes', [
            'fechaProceso' => '2026-10-24',
            'dryRun' => true,
            'items' => [[
                'code' => 'CLI-001',
                'nombre' => 'Nombre que no debe persistir',
                'tipoCliente' => ['code' => 'EMPRESA'],
            ]],
        ], $headers);

        $dryRun->assertOk()
            ->assertJsonPath('resultado.dryRun', true)
            ->assertJsonPath('resultado.resultados.0.accion', 'modificacion');
        $this->assertDatabaseHas('PQ_PARTES_CLIENTES', [
            'code' => 'CLI-001',
            'nombre' => 'Acme SA',
        ]);
    }

    public function test_rejects_empty_request_and_strict_boolean_values(): void
    {
        $headers = $this->authHeaders();

        $this->postJson('/api/v1/integracion/maestros/tipos-tarea', [], $headers)
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.requestVacio');

        $this->postJson('/api/v1/integracion/maestros/tipos-tarea', [
            'fechaProceso' => '2026-10-24',
            'dryRun' => 'false',
            'items' => [[
                'code' => 'TAR-001',
                'descripcion' => 'Administración',
            ]],
        ], $headers)
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.contratoInvalido');
    }
}
