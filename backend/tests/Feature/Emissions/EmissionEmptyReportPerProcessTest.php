<?php

namespace Tests\Feature\Emissions;

use Database\Seeders\PqEmissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Smoke lógico: emisión PDF consolidada con dataset vacío (0 filas) por proceso host.
 */
final class EmissionEmptyReportPerProcessTest extends TestCase
{
    use RefreshDatabase;

    private const GHOST_TAREA_ID = 9_999_999;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    /** @return array<string, string> */
    private function authHeaders(string $token): array
    {
        return array_merge($this->tenantHeaders(), ['Authorization' => 'Bearer '.$token]);
    }

    private function loginAdmin(): string
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());
        $login->assertStatus(200);

        return (string) $login->json('resultado.token');
    }

    private function seedEmittableReport(string $processCode, string $reportCode): void
    {
        DB::table('pq_emission_reports')
            ->where('process_code', $processCode)
            ->where('is_standard', 1)
            ->update(['is_principal' => 0]);

        DB::table('pq_emission_reports')->updateOrInsert(
            [
                'process_code' => $processCode,
                'report_code' => $reportCode,
            ],
            [
                'name' => 'Smoke empty dataset',
                'is_standard' => 0,
                'is_principal' => 1,
                'visible_mobile' => true,
                'layout_definition' => '<XtraReportsLayoutSerializer/>',
                'layout_mime' => 'application/xml',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /** @return array<string, mixed> */
    private function consultaHostContext(string $hoy): array
    {
        return [
            'fechaDesde' => $hoy,
            'fechaHasta' => $hoy,
            'clienteId' => null,
            'usuarioId' => null,
            'tipoTareaId' => null,
            'estadoCerrado' => 'todas',
            'selectedTareaIds' => [self::GHOST_TAREA_ID],
        ];
    }

    /** @return array<string, mixed> */
    private function paqueteHostContext(string $hoy, int $clienteId): array
    {
        return [
            'fechaDesde' => $hoy,
            'fechaHasta' => $hoy,
            'clienteId' => $clienteId,
            'tipoClienteId' => null,
            'selectedTareaIds' => [self::GHOST_TAREA_ID],
        ];
    }

    public function test_empty_pdf_consulta_detallada(): void
    {
        $token = $this->loginAdmin();
        $hoy = now()->toDateString();
        $process = PqEmissionSeeder::PROCESS_CODE;
        $this->seedEmittableReport($process, 'partes.consultaDetallada.smoke.empty');

        $emit = $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => $process,
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->consultaHostContext($hoy),
        ], $this->authHeaders($token));

        $emit->assertStatus(200)
            ->assertJsonPath('resultado.item.status', 'done')
            ->assertJsonPath('resultado.item.datasetRowCount', 0);

        $jobId = (string) $emit->json('resultado.item.jobId');
        $this->get('/api/v1/emissions/jobs/'.$jobId.'/download', $this->authHeaders($token))
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_empty_pdf_paquete_horas(): void
    {
        $token = $this->loginAdmin();
        $hoy = now()->toDateString();
        $process = PqEmissionSeeder::PROCESS_CODE_PAQUETE_HORAS;
        $this->seedEmittableReport($process, 'partes.paqueteHoras.smoke.empty');

        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCE',
            'descripcion' => 'Tipo empty smoke',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLE',
            'nombre' => 'Cliente empty smoke',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $emit = $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => $process,
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->paqueteHostContext($hoy, $clienteId),
        ], $this->authHeaders($token));

        $emit->assertStatus(200)
            ->assertJsonPath('resultado.item.status', 'done')
            ->assertJsonPath('resultado.item.datasetRowCount', 0);

        $jobId = (string) $emit->json('resultado.item.jobId');
        $this->get('/api/v1/emissions/jobs/'.$jobId.'/download', $this->authHeaders($token))
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }
}
