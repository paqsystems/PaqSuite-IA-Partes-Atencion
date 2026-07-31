<?php

namespace Tests\Feature\Partes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiV1PartesInformeTest extends TestCase
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

    public function test_dashboard_y_agrupado_empty_y_params(): void
    {
        $token = $this->loginAdmin();
        $params = $this->getJson('/api/v1/partes/parametros/dashboard', $this->authHeaders($token));
        $params->assertStatus(200)
            ->assertJsonPath('resultado.topN', 10)
            ->assertJsonPath('resultado.refreshSeg', 60);

        $mes = now()->format('Y-m');
        $dash = $this->getJson('/api/v1/partes/dashboard?mes='.$mes, $this->authHeaders($token));
        $dash->assertStatus(200)
            ->assertJsonPath('resultado.totalMinutos', 0)
            ->assertJsonPath('resultado.cantidadTareas', 0);

        $this->getJson('/api/v1/partes/informes/agrupado?eje=cliente', $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.tarea.fechasRequeridas');

        $desde = now()->startOfMonth()->toDateString();
        $hasta = now()->endOfMonth()->toDateString();
        $agr = $this->getJson(
            '/api/v1/partes/informes/agrupado?eje=cliente&fechaDesde='.$desde.'&fechaHasta='.$hasta,
            $this->authHeaders($token)
        );
        $agr->assertStatus(200)->assertJsonPath('resultado.total', 0);

        $this->getJson(
            '/api/v1/partes/informes/agrupado?eje=fecha&fechaDesde='.$desde.'&fechaHasta='.$hasta,
            $this->authHeaders($token)
        )->assertStatus(422)->assertJsonPath('respuesta', 'partes.consulta.granularidadRequerida');

        $paq = $this->getJson(
            '/api/v1/partes/informes/paquete-horas?fechaDesde='.$desde.'&fechaHasta='.$hasta,
            $this->authHeaders($token)
        );
        $paq->assertStatus(200)->assertJsonPath('resultado.totalMinutos', 0);
    }

    public function test_informe_detallado_con_dato(): void
    {
        $token = $this->loginAdmin();
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCI',
            'descripcion' => 'T',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLI',
            'nombre' => 'Cli Inf',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');
        $asistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');
        $hoy = now()->toDateString();
        $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $asistenteId,
            'clienteId' => $clienteId,
            'tipoTareaId' => $tipoId,
            'fecha' => $hoy,
            'duracionMinutos' => 30,
            'observacion' => 'Inf',
        ], $this->authHeaders($token))->assertStatus(201);

        $list = $this->getJson(
            '/api/v1/partes/informes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $list->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, (int) $list->json('resultado.total'));

        $agr = $this->getJson(
            '/api/v1/partes/informes/agrupado?eje=cliente&fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $agr->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, (int) $agr->json('resultado.total'));
    }

    public function test_delimitacion_asistente_y_cliente_en_informes(): void
    {
        $this->seed();

        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCD',
            'descripcion' => 'Tipo D',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteAId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLA',
            'nombre' => 'Cliente A',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteBUser = \App\Models\User::factory()->create([
            'usuario' => 'cliInf',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $clienteBId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => $clienteBUser->id,
            'code' => 'CLB',
            'nombre' => 'Cliente B',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $asstUser = \App\Models\User::factory()->create([
            'usuario' => 'asstInf',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $asstId = DB::table('PQ_PARTES_USUARIOS')->insertGetId([
            'user_id' => $asstUser->id,
            'code' => 'AINF',
            'nombre' => 'Asist Inf',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
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

        foreach ([
            ['usuarioId' => $adminAsistenteId, 'clienteId' => $clienteAId, 'obs' => 'Admin A'],
            ['usuarioId' => $asstId, 'clienteId' => $clienteBId, 'obs' => 'Asst B'],
        ] as $payload) {
            $this->postJson('/api/v1/partes/tareas', [
                'usuarioId' => $payload['usuarioId'],
                'clienteId' => $payload['clienteId'],
                'tipoTareaId' => $tipoId,
                'fecha' => $hoy,
                'duracionMinutos' => 15,
                'observacion' => $payload['obs'],
            ], $this->authHeaders($adminToken))->assertStatus(201);
        }

        $asstLogin = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'asstInf',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $asstLogin->assertStatus(200)
            ->assertJsonPath('resultado.partes.esSupervisor', false)
            ->assertJsonPath('resultado.partes.asistenteId', $asstId);
        $asstToken = (string) $asstLogin->json('resultado.token');

        $this->app['auth']->forgetGuards();
        $asstList = $this->getJson(
            '/api/v1/partes/informes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($asstToken)
        );
        $asstList->assertStatus(200);
        $this->assertSame(1, (int) $asstList->json('resultado.total'));
        $this->assertSame('Asst B', (string) $asstList->json('resultado.items.0.observacion'));

        $cliToken = (string) $this->postJson('/api/v1/auth/login', [
            'usuario' => 'cliInf',
            'password' => 'Secret123!',
        ], $this->tenantHeaders())->json('resultado.token');

        $this->app['auth']->forgetGuards();
        $cliList = $this->getJson(
            '/api/v1/partes/informes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($cliToken)
        );
        $cliList->assertStatus(200);
        $this->assertSame(1, (int) $cliList->json('resultado.total'));
        $this->assertSame('Asst B', (string) $cliList->json('resultado.items.0.observacion'));

        $paq = $this->getJson(
            '/api/v1/partes/informes/paquete-horas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($cliToken)
        );
        $paq->assertStatus(200)->assertJsonPath('resultado.totalMinutos', 15);
    }
}
