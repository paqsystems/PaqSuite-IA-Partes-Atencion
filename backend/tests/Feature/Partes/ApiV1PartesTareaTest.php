<?php

namespace Tests\Feature\Partes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiV1PartesTareaTest extends TestCase
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
            'code' => 'TC1',
            'descripcion' => 'Tipo',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CL1',
            'nombre' => 'Cliente Uno',
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

    public function test_list_requiere_fechas_y_tramo(): void
    {
        $token = $this->login();
        $this->getJson('/api/v1/partes/tareas', $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.tarea.fechasRequeridas');

        $tramo = $this->getJson('/api/v1/partes/parametros/duracion-tramo', $this->authHeaders($token));
        $tramo->assertStatus(200)->assertJsonPath('resultado.tramoMinutos', 15);
    }

    public function test_crud_cerrar_y_conflicto_version(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $hoy = now()->toDateString();

        $create = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 15,
            'observacion' => 'Alta prueba',
            'sinCargo' => false,
            'presencial' => false,
        ], $this->authHeaders($token));
        $create->assertStatus(201);
        $id = (int) $create->json('resultado.item.id');
        $rv = (string) $create->json('resultado.item.rowVersion');
        $this->assertNotSame('', $rv);
        $create->assertJsonPath('resultado.item.esTarea', true);
        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->value('es_tarea'));

        $list = $this->getJson(
            '/api/v1/partes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $list->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, (int) $list->json('resultado.total'));
        $listItem = collect($list->json('resultado.items'))->firstWhere('id', $id);
        $this->assertNotNull($listItem);
        $this->assertTrue((bool) $listItem['esTarea']);

        $stale = $this->putJson("/api/v1/partes/tareas/{$id}", [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 30,
            'observacion' => 'Update stale',
            'rowVersion' => '0000000000000000',
        ], $this->authHeaders($token));
        $stale->assertStatus(409)->assertJsonPath('respuesta', 'partes.tarea.conflictoVersion');

        $ok = $this->putJson("/api/v1/partes/tareas/{$id}", [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 30,
            'observacion' => 'Update ok',
            'rowVersion' => $rv,
        ], $this->authHeaders($token));
        $ok->assertStatus(200);
        $rv2 = (string) $ok->json('resultado.item.rowVersion');
        $this->assertNotSame($rv, $rv2);

        $cerrar = $this->postJson("/api/v1/partes/tareas/{$id}/cerrar", [
            'rowVersion' => $rv2,
        ], $this->authHeaders($token));
        $cerrar->assertStatus(200)->assertJsonPath('resultado.item.cerrado', true);
        $rv3 = (string) $cerrar->json('resultado.item.rowVersion');

        $editCerrada = $this->putJson("/api/v1/partes/tareas/{$id}", [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 45,
            'observacion' => 'No',
            'rowVersion' => $rv3,
        ], $this->authHeaders($token));
        $editCerrada->assertStatus(422)->assertJsonPath('respuesta', 'partes.tarea.cerradaNoEditable');
    }

    public function test_asistente_no_puede_cargar_otro_owner_ni_cerrar(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'usuario' => 'asstplain',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
        ]);
        $asistenteId = DB::table('PQ_PARTES_USUARIOS')->insertGetId([
            'user_id' => $user->id,
            'code' => 'A001',
            'nombre' => 'Asistente',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $adminAsistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');
        $cat = $this->seedCatalogos();

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'asstplain',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $login->assertStatus(200);
        $token = (string) $login->json('resultado.token');
        $hoy = now()->toDateString();

        $forbidden = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $adminAsistenteId,
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 15,
            'observacion' => 'Ajena',
        ], $this->authHeaders($token));
        $forbidden->assertStatus(403)->assertJsonPath('respuesta', 'partes.tarea.forbiddenOwner');

        $own = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $asistenteId,
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 15,
            'observacion' => 'Propia',
        ], $this->authHeaders($token));
        $own->assertStatus(201);
        $id = (int) $own->json('resultado.item.id');
        $rv = (string) $own->json('resultado.item.rowVersion');

        $cerrar = $this->postJson("/api/v1/partes/tareas/{$id}/cerrar", [
            'rowVersion' => $rv,
        ], $this->authHeaders($token));
        $cerrar->assertStatus(403)->assertJsonPath('respuesta', 'partes.tarea.soloSupervisor');
    }

    public function test_fecha_futura_requiere_confirmacion_y_duracion_invalida(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $futura = now()->addDays(2)->toDateString();

        $warn = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $futura,
            'duracionMinutos' => 15,
            'observacion' => 'Futura',
        ], $this->authHeaders($token));
        $warn->assertStatus(422)->assertJsonPath('respuesta', 'partes.tarea.fechaFuturaConfirmacion');

        $ok = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $futura,
            'duracionMinutos' => 15,
            'observacion' => 'Futura ok',
            'confirmarFechaFutura' => true,
        ], $this->authHeaders($token));
        $ok->assertStatus(201);

        $badDur = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => now()->toDateString(),
            'duracionMinutos' => 10,
            'observacion' => 'Tramo malo',
        ], $this->authHeaders($token));
        $badDur->assertStatus(422)->assertJsonPath('respuesta', 'partes.tarea.duracionInvalida');
    }

    public function test_cliente_no_puede_operar_tareas(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'usuario' => 'cliTarea',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCX',
            'descripcion' => 'Tipo X',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $user->id,
            'code' => 'CLX',
            'nombre' => 'Cliente X',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'cliTarea',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $login->assertStatus(200)->assertJsonPath('resultado.partes.tipoFuncional', 'cliente');
        $token = (string) $login->json('resultado.token');

        $this->getJson('/api/v1/partes/tareas?fechaDesde='.now()->toDateString().'&fechaHasta='.now()->toDateString(), $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'partes.maestros.forbidden');

        $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => 1,
            'clienteId' => 1,
            'tipoTareaId' => 1,
            'fecha' => now()->toDateString(),
            'duracionMinutos' => 15,
            'observacion' => 'No',
        ], $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'partes.maestros.forbidden');
    }

    public function test_list_excluye_compras_y_upsert_fuerza_es_tarea(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $hoy = now()->toDateString();

        $compraId = (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->insertGetId([
            'usuario_id' => $cat['asistenteId'],
            'cliente_id' => $cat['clienteId'],
            'tipo_tarea_id' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracion_minutos' => 60,
            'sin_cargo' => false,
            'presencial' => false,
            'observacion' => 'Compra horas',
            'cerrado' => false,
            'es_tarea' => false,
            'row_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $list = $this->getJson(
            '/api/v1/partes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $list->assertStatus(200);
        $ids = collect($list->json('resultado.items'))->pluck('id')->all();
        $this->assertNotContains($compraId, $ids);

        $create = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 15,
            'observacion' => 'Tarea real',
            'sinCargo' => false,
            'presencial' => false,
        ], $this->authHeaders($token));
        $create->assertStatus(201)->assertJsonPath('resultado.item.esTarea', true);
        $tareaId = (int) $create->json('resultado.item.id');
        $rv = (string) $create->json('resultado.item.rowVersion');

        DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $tareaId)->update(['es_tarea' => false]);

        $update = $this->putJson("/api/v1/partes/tareas/{$tareaId}", [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 30,
            'observacion' => 'Edit fuerza es_tarea',
            'rowVersion' => $rv,
        ], $this->authHeaders($token));
        $update->assertStatus(200)->assertJsonPath('resultado.item.esTarea', true);
        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $tareaId)->value('es_tarea'));
    }
}
