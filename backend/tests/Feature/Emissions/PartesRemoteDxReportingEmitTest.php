<?php

namespace Tests\Feature\Emissions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class PartesRemoteDxReportingEmitTest extends TestCase
{
    use RefreshDatabase;

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

    private function login(): string
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());
        $login->assertStatus(200);

        return (string) $login->json('resultado.token');
    }

    public function test_emit_remote_posts_report_code_to_sidecar(): void
    {
        config([
            'paqsuite.emissions.dxReporting.mode' => 'remote',
            'paqsuite.emissions.dxReporting.serviceUrl' => 'http://127.0.0.1:5055/api/v1/dx-reporting',
            'paqsuite.emissions.dxReporting.apiKey' => '',
            'paqsuite.emissions.dxReporting.timeoutSeconds' => 5,
        ]);

        $seenReportId = null;
        Http::fake([
            'http://127.0.0.1:5055/api/v1/dx-reporting/render' => function ($request) use (&$seenReportId) {
                $payload = $request->data();
                $seenReportId = $payload['reportId'] ?? null;

                return Http::response([
                    'fileName' => 'partes.pdf',
                    'mimeType' => 'application/pdf',
                    'contentBase64' => base64_encode('%PDF-1.4 remote'),
                ], 200);
            },
        ]);

        $token = $this->login();

        $this->app->forgetInstance(\PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine::class);
        $this->app->singleton(
            \PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine::class,
            fn ($app) => \App\Services\Emissions\EmissionDxReportingEngineFactory::make($app),
        );
        $this->app->forgetInstance(\PaqSuite\LaravelCore\Emissions\EmissionOrchestrator::class);
        $reportId = (int) DB::table('pq_emission_reports')->insertGetId([
            'process_code' => 'partes.informes.consultaDetallada',
            'report_code' => 'partes.consultaDetallada.sidecar',
            'name' => 'Sidecar layout',
            'is_standard' => 0,
            'is_principal' => 1,
            'visible_mobile' => true,
            'layout_definition' => '<XtraReportsLayoutSerializer/>',
            'layout_mime' => 'application/xml',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hoy = now()->toDateString();
        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'reportId' => $reportId,
            'hostContext' => [
                'fechaDesde' => $hoy,
                'fechaHasta' => $hoy,
                'clienteId' => null,
                'usuarioId' => null,
                'tipoTareaId' => null,
                'estadoCerrado' => 'todas',
            ],
        ], $this->authHeaders($token))
            ->assertStatus(200)
            ->assertJsonPath('resultado.item.status', 'done');

        $this->assertSame('partes.consultaDetallada.sidecar', $seenReportId);
        Http::assertSentCount(1);
    }
}
