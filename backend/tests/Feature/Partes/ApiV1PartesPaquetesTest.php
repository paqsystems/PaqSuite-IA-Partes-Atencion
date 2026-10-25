<?php

namespace Tests\Feature\Partes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiV1PartesPaquetesTest extends TestCase
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

    /**
     * @return array{clienteId: int, tipoId: int, asistenteId: int}
     */
    private function seedCatalogos(): array
    {
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCPQ',
            'descripcion' => 'Tipo PQ',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLPQ',
            'nombre' => 'Cliente Paquete',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');
        $asistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');

        return compact('clienteId', 'tipoId', 'asistenteId');
    }

    public function test_asistente_y_cliente_reciben_403(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'usuario' => 'asstPq',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $user->id,
            'code' => 'APQ',
            'nombre' => 'Asistente PQ',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'asstPq',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $login->assertStatus(200);
        $token = (string) $login->json('resultado.token');
        $hoy = now()->toDateString();

        $this->getJson(
            '/api/v1/partes/paquetes?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        )->assertStatus(403)->assertJsonPath('respuesta', 'partes.paquete.soloSupervisor');

        $cli = User::factory()->create([
            'usuario' => 'cliPq',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCC',
            'descripcion' => 'Tipo C',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $cli->id,
            'code' => 'CLC',
            'nombre' => 'Cliente C',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cliLogin = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'cliPq',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $cliLogin->assertStatus(200);
        $cliToken = (string) $cliLogin->json('resultado.token');
        $this->getJson(
            '/api/v1/partes/paquetes?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($cliToken)
        )->assertStatus(403);
    }

    public function test_list_requiere_fechas(): void
    {
        $token = $this->login();
        $this->getJson('/api/v1/partes/paquetes', $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.paquete.fechasRequeridas');
    }

    public function test_crud_ignora_es_tarea_y_usuario_inmutable(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $hoy = now()->toDateString();

        $create = $this->postJson('/api/v1/partes/paquetes', [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 8 * 60,
            'observacion' => 'Compra 8 horas',
            'esTarea' => true,
            'usuarioId' => 999,
        ], $this->authHeaders($token));
        $create->assertStatus(201);
        $id = (int) $create->json('resultado.item.id');
        $rv = (string) $create->json('resultado.item.rowVersion');
        $this->assertFalse((bool) $create->json('resultado.item.esTarea'));
        $this->assertFalse((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->value('es_tarea'));
        $this->assertSame($cat['asistenteId'], (int) $create->json('resultado.item.usuarioId'));

        $list = $this->getJson(
            '/api/v1/partes/paquetes?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $list->assertStatus(200);
        $this->assertNotNull(collect($list->json('resultado.items'))->firstWhere('id', $id));

        $tareas = $this->getJson(
            '/api/v1/partes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $tareas->assertStatus(200);
        $this->assertNull(collect($tareas->json('resultado.items'))->firstWhere('id', $id));

        $stale = $this->putJson("/api/v1/partes/paquetes/{$id}", [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 9 * 60,
            'observacion' => 'Stale',
            'rowVersion' => '0000000000000000',
        ], $this->authHeaders($token));
        $stale->assertStatus(409)->assertJsonPath('respuesta', 'partes.paquete.conflictoVersion');

        $ok = $this->putJson("/api/v1/partes/paquetes/{$id}", [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 9 * 60,
            'observacion' => 'Update ok',
            'usuarioId' => 1,
            'rowVersion' => $rv,
        ], $this->authHeaders($token));
        $ok->assertStatus(200);
        $this->assertSame($cat['asistenteId'], (int) $ok->json('resultado.item.usuarioId'));
        $this->assertSame(9 * 60, (int) $ok->json('resultado.item.duracionMinutos'));
        $rv2 = (string) $ok->json('resultado.item.rowVersion');

        $this->deleteJson("/api/v1/partes/paquetes/{$id}", [
            'rowVersion' => $rv2,
        ], $this->authHeaders($token))->assertStatus(200);
        $this->assertNull(DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first());
    }

    public function test_fecha_futura_requiere_confirmacion(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $futura = now()->addDays(3)->toDateString();

        $this->postJson('/api/v1/partes/paquetes', [
            'clienteId' => $cat['clienteId'],
            'fecha' => $futura,
            'duracionMinutos' => 8 * 60,
            'observacion' => 'Futura',
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.paquete.fechaFuturaConfirmacion');

        $this->postJson('/api/v1/partes/paquetes', [
            'clienteId' => $cat['clienteId'],
            'fecha' => $futura,
            'duracionMinutos' => 8 * 60,
            'observacion' => 'Futura ok',
            'confirmarFechaFutura' => true,
        ], $this->authHeaders($token))->assertStatus(201);
    }

    public function test_not_paquete_sobre_tarea(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $hoy = now()->toDateString();
        $tarea = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 8 * 60,
            'observacion' => 'Es tarea',
        ], $this->authHeaders($token));
        $tarea->assertStatus(201);
        $id = (int) $tarea->json('resultado.item.id');
        $rv = (string) $tarea->json('resultado.item.rowVersion');

        $this->getJson("/api/v1/partes/paquetes/{$id}", $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.paquete.notPaquete');
        $this->putJson("/api/v1/partes/paquetes/{$id}", [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 9 * 60,
            'observacion' => 'No',
            'rowVersion' => $rv,
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.paquete.notPaquete');
        $this->deleteJson("/api/v1/partes/paquetes/{$id}", [
            'rowVersion' => $rv,
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.paquete.notPaquete');
    }

    public function test_update_conserva_cliente_historico_inhabilitado(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $hoy = now()->toDateString();
        $create = $this->postJson('/api/v1/partes/paquetes', [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 8 * 60,
            'observacion' => 'Histórico',
        ], $this->authHeaders($token));
        $create->assertStatus(201);
        $id = (int) $create->json('resultado.item.id');
        $rv = (string) $create->json('resultado.item.rowVersion');

        DB::table('PQ_PARTES_CLIENTES')->where('id', $cat['clienteId'])->update([
            'inhabilitado' => true,
            'activo' => false,
        ]);

        $keep = $this->putJson("/api/v1/partes/paquetes/{$id}", [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 9 * 60,
            'observacion' => 'Sigue histórico',
            'rowVersion' => $rv,
        ], $this->authHeaders($token));
        $keep->assertStatus(200);

        $otro = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLINH',
            'nombre' => 'Inhabilitado',
            'tipo_cliente_id' => DB::table('PQ_PARTES_CLIENTES')->where('id', $cat['clienteId'])->value('tipo_cliente_id'),
            'email' => null,
            'activo' => false,
            'inhabilitado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->putJson("/api/v1/partes/paquetes/{$id}", [
            'clienteId' => $otro,
            'fecha' => $hoy,
            'duracionMinutos' => 9 * 60,
            'observacion' => 'Cambio inhabilitado',
            'rowVersion' => (string) $keep->json('resultado.item.rowVersion'),
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.paquete.clienteNoUsable');
    }

    public function test_alta_paquete_aparece_en_informe(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $hoy = now()->toDateString();
        $this->postJson('/api/v1/partes/paquetes', [
            'clienteId' => $cat['clienteId'],
            'fecha' => $hoy,
            'duracionMinutos' => 8 * 60,
            'observacion' => 'Compra informe',
        ], $this->authHeaders($token))->assertStatus(201);

        $paq = $this->getJson(
            '/api/v1/partes/informes/paquete-horas?fechaDesde='.$hoy.'&fechaHasta='.$hoy.'&clienteId='.$cat['clienteId'],
            $this->authHeaders($token)
        );
        $paq->assertStatus(200);
        $movs = collect($paq->json('resultado.items'))->where('esSaldoInicial', false)->values();
        $this->assertGreaterThanOrEqual(1, $movs->count());
        $this->assertFalse((bool) $movs[0]['esTarea']);
        $this->assertSame(-(8 * 60), (int) $movs[0]['saldo']);
    }
}
