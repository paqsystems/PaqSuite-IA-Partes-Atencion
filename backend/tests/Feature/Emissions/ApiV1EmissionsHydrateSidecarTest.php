<?php

namespace Tests\Feature\Emissions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ApiV1EmissionsHydrateSidecarTest extends TestCase
{
    use RefreshDatabase;

    private const PROCESS = 'partes.informes.consultaDetallada';

    private const HYDRATE = '/api/v1/emissions/design/processes/'.self::PROCESS.'/reports/hydrate-sidecar';

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

    public function test_hydrate_requires_auth(): void
    {
        $this->seed();
        $this->postJson(self::HYDRATE, [], $this->tenantHeaders())->assertStatus(401);
    }

    public function test_hydrate_calls_sidecar_and_returns_counters(): void
    {
        config([
            'services.dx_reporting_sidecar_url' => 'http://127.0.0.1:5055',
            'paqsuite.emissions.designerSidecarToken' => 'test-token',
        ]);

        Http::fake([
            'http://127.0.0.1:5055/api/layout/put' => Http::response(['ok' => true, 'skipped' => false], 200),
        ]);

        $token = $this->login();
        $xml = '<XtraReportsLayoutSerializer Name="hydrate-test"/>';
        \Illuminate\Support\Facades\DB::table('pq_emission_reports')->insert([
            'process_code' => self::PROCESS,
            'report_code' => 'partes.hydrate.test',
            'name' => 'Hydrate test',
            'is_standard' => 0,
            'is_principal' => 0,
            'visible_mobile' => 1,
            'layout_definition' => $xml,
            'layout_mime' => 'application/xml',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson(self::HYDRATE, [], $this->authHeaders($token));
        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.processCode', self::PROCESS);

        $hydrated = (int) $response->json('resultado.hydrated');
        $this->assertGreaterThanOrEqual(1, $hydrated);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:5055/api/layout/put'
                && $request->hasHeader('X-Paq-Sidecar-Schema-Token', 'test-token');
        });
    }

    public function test_hydrate_filter_single_report_code(): void
    {
        config(['services.dx_reporting_sidecar_url' => 'http://127.0.0.1:5055']);

        Http::fake([
            'http://127.0.0.1:5055/api/layout/put' => Http::sequence()
                ->push(['ok' => true, 'skipped' => false], 200)
                ->push(['ok' => true, 'skipped' => true], 200),
        ]);

        $token = $this->login();
        $xml = '<XtraReportsLayoutSerializer Name="only-one"/>';
        \Illuminate\Support\Facades\DB::table('pq_emission_reports')->insert([
            'process_code' => self::PROCESS,
            'report_code' => 'partes.hydrate.only',
            'name' => 'Solo uno',
            'is_standard' => 0,
            'is_principal' => 0,
            'visible_mobile' => 1,
            'layout_definition' => $xml,
            'layout_mime' => 'application/xml',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson(self::HYDRATE, ['reportCodes' => ['partes.hydrate.only']], $this->authHeaders($token))
            ->assertStatus(200)
            ->assertJsonPath('resultado.hydrated', 1);

        Http::assertSentCount(1);

        $again = $this->postJson(
            self::HYDRATE,
            ['reportCodes' => ['partes.hydrate.only']],
            $this->authHeaders($token),
        );
        $again->assertStatus(200);
        $this->assertSame(0, (int) $again->json('resultado.hydrated'));
        $this->assertSame(1, (int) $again->json('resultado.skipped'));
    }
}