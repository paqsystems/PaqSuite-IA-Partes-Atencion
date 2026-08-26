<?php

namespace Tests\Feature\Emissions;

use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Services\Emissions\PartesConsultaDetalladaEmissionPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\EmissionDatasetPortRegistry;
use Tests\TestCase;

class ApiV1EmissionsPartesTest extends TestCase
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

    private function login(string $usuario = 'admin', string $password = 'Paqsystems'): string
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => $usuario,
            'password' => $password,
        ], $this->tenantHeaders());
        $login->assertStatus(200);

        return (string) $login->json('resultado.token');
    }

    /** @return array<string, mixed> */
    private function hostContext(?string $hoy = null): array
    {
        $hoy ??= now()->toDateString();

        return [
            'fechaDesde' => $hoy,
            'fechaHasta' => $hoy,
            'clienteId' => null,
            'usuarioId' => null,
            'tipoTareaId' => null,
            'estadoCerrado' => 'todas',
        ];
    }

    public function test_get_process_seed_y_401_sin_token(): void
    {
        $this->getJson(
            '/api/v1/emissions/processes/partes.informes.consultaDetallada',
            $this->tenantHeaders()
        )->assertStatus(401);

        $token = $this->login();
        $this->getJson(
            '/api/v1/emissions/processes/partes.informes.consultaDetallada',
            $this->authHeaders($token)
        )
            ->assertStatus(200)
            ->assertJsonPath('resultado.item.processCode', 'partes.informes.consultaDetallada')
            ->assertJsonPath('resultado.item.menuProcessCode', 'partes_consulta_detallada')
            ->assertJsonPath('resultado.item.requiresPreview', false)
            ->assertJsonPath('resultado.item.allowsSegmented', false)
            ->assertJsonPath('resultado.item.allowsConsolidated', true);

        $channels = $this->getJson(
            '/api/v1/emissions/processes/partes.informes.consultaDetallada',
            $this->authHeaders($token)
        )->json('resultado.item.channels');
        $this->assertEqualsCanonicalizing(['pdf', 'print', 'excel', 'csv', 'mail'], $channels);
        $this->assertNotContains('zip', $channels);

        $menuType = (string) DB::table('pq_menus')->where('codigo', 'partes_disenador_emisiones')->value('process_type');
        $this->assertSame('A', $menuType);
        $this->assertNotSame('E', $menuType);
    }

    public function test_capability_off_4704(): void
    {
        $token = $this->login();
        DB::table('pq_parametros_gral')
            ->where('programa', 'Emission')
            ->where('clave', 'EmissionEnabled')
            ->update(['valor_string' => 'N']);

        $this->getJson(
            '/api/v1/emissions/processes/partes.informes.consultaDetallada',
            $this->authHeaders($token)
        )
            ->assertStatus(403)
            ->assertJsonPath('error', 4704)
            ->assertJsonPath('respuesta', 'emission.capabilityDisabled');
    }

    public function test_sin_puerto_4706(): void
    {
        $token = $this->login();
        $this->app->singleton(EmissionDatasetPortRegistry::class, fn () => new EmissionDatasetPortRegistry());

        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->hostContext(),
        ], $this->authHeaders($token))
            ->assertStatus(409)
            ->assertJsonPath('error', 4706);
    }

    public function test_fechas_vacias_4701(): void
    {
        $token = $this->login();
        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => [
                'fechaDesde' => '',
                'fechaHasta' => '',
                'clienteId' => null,
                'usuarioId' => null,
                'tipoTareaId' => null,
                'estadoCerrado' => 'todas',
            ],
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('error', 4701);
    }

    public function test_sin_menu_consulta_4703(): void
    {
        $token = $this->login();
        DB::table('pq_menus')->where('codigo', 'partes_consulta_detallada')->delete();

        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->hostContext(),
        ], $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('error', 4703);
    }

    public function test_design_sin_acceso_total_4709_asistente_emite(): void
    {
        $this->seed();
        $asstUser = \App\Models\User::factory()->create([
            'usuario' => 'asstEmi',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $asstId = DB::table('PQ_PARTES_USUARIOS')->insertGetId([
            'user_id' => $asstUser->id,
            'code' => 'AEMI',
            'nombre' => 'Asist Emi',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $rolId = DB::table('pq_roles')->insertGetId([
            'codigo' => 'ASISTENTE_EMI',
            'nombre' => 'Asistente emision',
            'acceso_total' => false,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $empresaId = DB::table('pq_empresa')->value('id') ?? 1;
        DB::table('pq_permisos')->insert([
            'user_id' => $asstUser->id,
            'empresa_id' => $empresaId,
            'rol_id' => $rolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $asstToken = (string) $this->postJson('/api/v1/auth/login', [
            'usuario' => 'asstEmi',
            'password' => 'Secret123!',
        ], $this->tenantHeaders())->json('resultado.token');

        $this->app['auth']->forgetGuards();
        $this->getJson(
            '/api/v1/emissions/design/processes/partes.informes.consultaDetallada/reports',
            $this->authHeaders($asstToken)
        )
            ->assertStatus(403)
            ->assertJsonPath('error', 4709);

        $hoy = now()->toDateString();
        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->hostContext($hoy),
        ], $this->authHeaders($asstToken))
            ->assertStatus(200)
            ->assertJsonPath('resultado.item.status', 'done');

        $this->assertSame($asstId, (int) $asstId);
    }

    public function test_emit_pdf_todas_las_filas_y_host_context_por_job(): void
    {
        $token = $this->login();
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCE',
            'descripcion' => 'T',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLE',
            'nombre' => 'Cli Emi',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'erp_cliente' => 'ERP-E',
            'erp_articulo' => 'ART-E',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');
        $asistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');
        $hoy = now()->toDateString();

        for ($i = 0; $i < 55; $i++) {
            $this->postJson('/api/v1/partes/tareas', [
                'usuarioId' => $asistenteId,
                'clienteId' => $clienteId,
                'tipoTareaId' => $tipoId,
                'fecha' => $hoy,
                'duracionMinutos' => 15,
                'observacion' => 'Emi '.$i,
            ], $this->authHeaders($token))->assertStatus(201);
        }

        $paged = $this->getJson(
            '/api/v1/partes/informes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy.'&pageSize=50',
            $this->authHeaders($token)
        );
        $paged->assertStatus(200);
        $this->assertSame(55, (int) $paged->json('resultado.total'));
        $this->assertCount(50, $paged->json('resultado.items'));

        $emit = $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->hostContext($hoy),
        ], $this->authHeaders($token));
        $emit->assertStatus(200)
            ->assertJsonPath('resultado.item.status', 'done')
            ->assertJsonPath('resultado.item.datasetRowCount', 55);

        $jobId = (string) $emit->json('resultado.item.jobId');
        $this->get('/api/v1/emissions/jobs/'.$jobId.'/download', $this->authHeaders($token))
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        $port = $this->app->make(PartesConsultaDetalladaEmissionPort::class);
        $this->app['request']->replace([]);
        $this->app['request']->attributes->set(EnsurePartesFunctionalProfile::REQUEST_ATTR, [
            'tipoFuncional' => 'asistente',
            'asistenteId' => $asistenteId,
            'clienteId' => null,
            'esSupervisor' => true,
        ]);
        $dataset = $port->resolveDataset(new EmissionContext(
            'partes.informes.consultaDetallada',
            [1],
            1,
            'consolidated',
            'pdf',
            jobId: $jobId,
        ));
        $this->assertSame(55, $dataset->rowCount());
        $this->assertArrayHasKey('clienteCode', $dataset->rows[0]);
        $this->assertArrayNotHasKey('diaSemana', $dataset->rows[0]);
    }

    public function test_cliente_no_ve_otras_orgs_y_puede_emitir(): void
    {
        $this->seed();
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCC',
            'descripcion' => 'T',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteAId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLA',
            'nombre' => 'Cli A',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteBUser = \App\Models\User::factory()->create([
            'usuario' => 'cliEmi',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $clienteBId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => $clienteBUser->id,
            'code' => 'CLB',
            'nombre' => 'Cli B',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $rolClienteId = DB::table('pq_roles')->insertGetId([
            'codigo' => 'CLIENTE_EMI',
            'nombre' => 'Cliente emision',
            'acceso_total' => false,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $empresaId = DB::table('pq_empresa')->value('id') ?? 1;
        DB::table('pq_permisos')->insert([
            'user_id' => $clienteBUser->id,
            'empresa_id' => $empresaId,
            'rol_id' => $rolClienteId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $adminAsistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');
        $tipoId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');
        $hoy = now()->toDateString();
        $adminToken = (string) $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders())->json('resultado.token');

        foreach ([$clienteAId, $clienteBId] as $clienteId) {
            $this->postJson('/api/v1/partes/tareas', [
                'usuarioId' => $adminAsistenteId,
                'clienteId' => $clienteId,
                'tipoTareaId' => $tipoId,
                'fecha' => $hoy,
                'duracionMinutos' => 15,
                'observacion' => 'x',
            ], $this->authHeaders($adminToken))->assertStatus(201);
        }

        $cliToken = (string) $this->postJson('/api/v1/auth/login', [
            'usuario' => 'cliEmi',
            'password' => 'Secret123!',
        ], $this->tenantHeaders())->json('resultado.token');

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'pdf',
            'mode' => 'consolidated',
            'hostContext' => $this->hostContext($hoy),
        ], $this->authHeaders($cliToken))
            ->assertStatus(200)
            ->assertJsonPath('resultado.item.datasetRowCount', 1);
    }

    public function test_mail_sincrono_con_mail_to(): void
    {
        $token = $this->login();
        $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => 'partes.informes.consultaDetallada',
            'channel' => 'mail',
            'mode' => 'consolidated',
            'mailTo' => ['ops@example.com'],
            'hostContext' => $this->hostContext(),
        ], $this->authHeaders($token))
            ->assertStatus(200)
            ->assertJsonPath('resultado.item.status', 'done');
    }

    public function test_supervisor_design_ok(): void
    {
        $token = $this->login();
        $this->getJson(
            '/api/v1/emissions/design/processes/partes.informes.consultaDetallada/reports',
            $this->authHeaders($token)
        )
            ->assertStatus(200)
            ->assertJsonPath('resultado.designer', 'stub');
    }
}
