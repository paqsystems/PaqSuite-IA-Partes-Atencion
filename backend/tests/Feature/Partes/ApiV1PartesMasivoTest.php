<?php

namespace Tests\Feature\Partes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiV1PartesMasivoTest extends TestCase
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

    /**
     * @return array{clienteId: int, tipoId: int, asistenteId: int}
     */
    private function seedCatalogos(): array
    {
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCM',
            'descripcion' => 'Tipo M',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLM',
            'nombre' => 'Cliente Masivo',
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

    /** @return array{id: int, rowVersion: string} */
    private function createTarea(string $token, array $cat, string $obs): array
    {
        $hoy = now()->toDateString();
        $create = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cat['clienteId'],
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 15,
            'observacion' => $obs,
        ], $this->authHeaders($token));
        $create->assertStatus(201);

        return [
            'id' => (int) $create->json('resultado.item.id'),
            'rowVersion' => (string) $create->json('resultado.item.rowVersion'),
        ];
    }

    public function test_masivo_cerrar_reabrir_idempotente_y_ids(): void
    {
        $token = $this->loginAdmin();
        $cat = $this->seedCatalogos();
        $a = $this->createTarea($token, $cat, 'A');
        $b = $this->createTarea($token, $cat, 'B');
        $hoy = now()->toDateString();

        $ids = $this->getJson(
            '/api/v1/partes/tareas/ids?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $ids->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, (int) $ids->json('resultado.total'));

        $cerrar = $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $a['rowVersion']],
                ['id' => $b['id'], 'rowVersion' => $b['rowVersion']],
            ],
        ], $this->authHeaders($token));
        $cerrar->assertStatus(200);

        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('cerrado'));
        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $b['id'])->value('cerrado'));

        $rvA = $this->encodeCurrent($a['id']);
        $rvB = $this->encodeCurrent($b['id']);

        $idem = $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $rvA],
                ['id' => $b['id'], 'rowVersion' => $rvB],
            ],
        ], $this->authHeaders($token));
        $idem->assertStatus(200);

        $reabrir = $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'reabrir',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $this->encodeCurrent($a['id'])],
                ['id' => $b['id'], 'rowVersion' => $this->encodeCurrent($b['id'])],
            ],
        ], $this->authHeaders($token));
        $reabrir->assertStatus(200);
        $this->assertFalse((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('cerrado'));
    }

    public function test_masivo_conflicto_y_id_fantasma_atomico(): void
    {
        $token = $this->loginAdmin();
        $cat = $this->seedCatalogos();
        $a = $this->createTarea($token, $cat, 'C');
        $b = $this->createTarea($token, $cat, 'D');

        $stale = $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => '0000000000000000'],
                ['id' => $b['id'], 'rowVersion' => $b['rowVersion']],
            ],
        ], $this->authHeaders($token));
        $stale->assertStatus(409)->assertJsonPath('respuesta', 'partes.masivo.conflictoVersion');
        $this->assertFalse((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $b['id'])->value('cerrado'));

        $ghost = $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $a['rowVersion']],
                ['id' => 999999, 'rowVersion' => '0000000000000001'],
            ],
        ], $this->authHeaders($token));
        $ghost->assertStatus(422)->assertJsonPath('respuesta', 'partes.masivo.idInexistente');
        $this->assertFalse((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('cerrado'));
    }

    public function test_masivo_empty_tope_y_asistente_forbidden(): void
    {
        $token = $this->loginAdmin();
        $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [],
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.masivo.emptySelection');

        DB::table('pq_parametros_gral')->where('programa', 'Partes')->where('clave', 'PartesMasivoMaxIds')
            ->update(['valor_int' => 1]);
        $cat = $this->seedCatalogos();
        $a = $this->createTarea($token, $cat, 'E');
        $b = $this->createTarea($token, $cat, 'F');
        $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $a['rowVersion']],
                ['id' => $b['id'], 'rowVersion' => $b['rowVersion']],
            ],
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.masivo.topeExcedido');

        $user = User::factory()->create([
            'usuario' => 'asstmasivo',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $user->id,
            'code' => 'AM1',
            'nombre' => 'Asistente',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->app['auth']->forgetGuards();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'asstmasivo',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $login->assertStatus(200)
            ->assertJsonPath('resultado.user.usuario', 'asstmasivo')
            ->assertJsonPath('resultado.partes.esSupervisor', false)
            ->assertJsonPath('resultado.partes.code', 'AM1');
        $asstToken = (string) $login->json('resultado.token');
        $this->app['auth']->forgetGuards();
        $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [['id' => $a['id'], 'rowVersion' => $this->encodeCurrent($a['id'])]],
        ], $this->authHeaders($asstToken))
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'partes.masivo.forbidden');
    }

    public function test_masivo_actualizar_sin_cargo_y_tipo_sobre_cerradas(): void
    {
        $token = $this->loginAdmin();
        $cat = $this->seedCatalogos();
        $a = $this->createTarea($token, $cat, 'SC1');
        $b = $this->createTarea($token, $cat, 'SC2');

        $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $a['rowVersion']],
                ['id' => $b['id'], 'rowVersion' => $b['rowVersion']],
            ],
        ], $this->authHeaders($token))->assertStatus(200);

        $upd = $this->postJson('/api/v1/partes/tareas/masivo/actualizar', [
            'campos' => ['sinCargo' => true],
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $this->encodeCurrent($a['id'])],
                ['id' => $b['id'], 'rowVersion' => $this->encodeCurrent($b['id'])],
            ],
        ], $this->authHeaders($token));
        $upd->assertStatus(200);
        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('sin_cargo'));
        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $b['id'])->value('sin_cargo'));
        $this->assertTrue((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('cerrado'));

        $tipoOtro = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
            'code' => 'GEN2',
            'descripcion' => 'Genérico 2',
            'is_generico' => true,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $updTipo = $this->postJson('/api/v1/partes/tareas/masivo/actualizar', [
            'campos' => ['tipoTareaId' => $tipoOtro],
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $this->encodeCurrent($a['id'])],
                ['id' => $b['id'], 'rowVersion' => $this->encodeCurrent($b['id'])],
            ],
        ], $this->authHeaders($token));
        $updTipo->assertStatus(200);
        $this->assertSame($tipoOtro, (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('tipo_tarea_id'));
        $this->assertSame($tipoOtro, (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $b['id'])->value('tipo_tarea_id'));
    }

    public function test_masivo_actualizar_tipo_invalido_atomico(): void
    {
        $token = $this->loginAdmin();
        $cat = $this->seedCatalogos();

        $cliente2 = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CLM2',
            'nombre' => 'Cliente Masivo 2',
            'tipo_cliente_id' => DB::table('PQ_PARTES_CLIENTES')->where('id', $cat['clienteId'])->value('tipo_cliente_id'),
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tipoAsignado = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
            'code' => 'ESP1',
            'descripcion' => 'Específico CLM',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->insert([
            'cliente_id' => $cat['clienteId'],
            'tipo_tarea_id' => $tipoAsignado,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $a = $this->createTarea($token, $cat, 'T1');
        $hoy = now()->toDateString();
        $createB = $this->postJson('/api/v1/partes/tareas', [
            'usuarioId' => $cat['asistenteId'],
            'clienteId' => $cliente2,
            'tipoTareaId' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracionMinutos' => 15,
            'observacion' => 'T2',
        ], $this->authHeaders($token));
        $createB->assertStatus(201);
        $bId = (int) $createB->json('resultado.item.id');
        $bRv = (string) $createB->json('resultado.item.rowVersion');

        $prevTipoA = (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('tipo_tarea_id');
        $prevTipoB = (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $bId)->value('tipo_tarea_id');

        $fail = $this->postJson('/api/v1/partes/tareas/masivo/actualizar', [
            'campos' => ['tipoTareaId' => $tipoAsignado],
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $a['rowVersion']],
                ['id' => $bId, 'rowVersion' => $bRv],
            ],
        ], $this->authHeaders($token));
        $fail->assertStatus(422)->assertJsonPath('respuesta', 'partes.masivo.atributoInvalido');
        $this->assertSame($prevTipoA, (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $a['id'])->value('tipo_tarea_id'));
        $this->assertSame($prevTipoB, (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $bId)->value('tipo_tarea_id'));
    }

    public function test_masivo_actualizar_presencial_asistente_fecha(): void
    {
        $token = $this->loginAdmin();
        $cat = $this->seedCatalogos();
        $a = $this->createTarea($token, $cat, 'SH1');
        $b = $this->createTarea($token, $cat, 'SH2');

        $otroUser = User::factory()->create([
            'usuario' => 'asstshould',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
        ]);
        $otroAsistente = DB::table('PQ_PARTES_USUARIOS')->insertGetId([
            'user_id' => $otroUser->id,
            'code' => 'SHAS',
            'nombre' => 'Asistente Should',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $fechaNueva = now()->subDay()->toDateString();

        $upd = $this->postJson('/api/v1/partes/tareas/masivo/actualizar', [
            'campos' => [
                'presencial' => true,
                'usuarioId' => $otroAsistente,
                'fecha' => $fechaNueva,
            ],
            'items' => [
                ['id' => $a['id'], 'rowVersion' => $a['rowVersion']],
                ['id' => $b['id'], 'rowVersion' => $b['rowVersion']],
            ],
        ], $this->authHeaders($token));
        $upd->assertStatus(200);

        foreach ([$a['id'], $b['id']] as $id) {
            $row = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
            $this->assertTrue((bool) $row->presencial);
            $this->assertSame($otroAsistente, (int) $row->usuario_id);
            $this->assertSame($fechaNueva, substr((string) $row->fecha, 0, 10));
        }
    }

    public function test_masivo_excluye_compras_y_lote_rechaza_no_es_tarea(): void
    {
        $token = $this->loginAdmin();
        $cat = $this->seedCatalogos();
        $tarea = $this->createTarea($token, $cat, 'Tarea masivo');
        $hoy = now()->toDateString();

        $compraId = (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->insertGetId([
            'usuario_id' => $cat['asistenteId'],
            'cliente_id' => $cat['clienteId'],
            'tipo_tarea_id' => $cat['tipoId'],
            'fecha' => $hoy,
            'duracion_minutos' => 120,
            'sin_cargo' => false,
            'presencial' => false,
            'observacion' => 'Compra paquete',
            'cerrado' => false,
            'es_tarea' => false,
            'row_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $compraRv = $this->encodeCurrent($compraId);

        $list = $this->getJson(
            '/api/v1/partes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $list->assertStatus(200);
        $listIds = collect($list->json('resultado.items'))->pluck('id')->all();
        $this->assertContains($tarea['id'], $listIds);
        $this->assertNotContains($compraId, $listIds);

        $ids = $this->getJson(
            '/api/v1/partes/tareas/ids?fechaDesde='.$hoy.'&fechaHasta='.$hoy,
            $this->authHeaders($token)
        );
        $ids->assertStatus(200);
        $idList = collect($ids->json('resultado.items'))->pluck('id')->all();
        $this->assertContains($tarea['id'], $idList);
        $this->assertNotContains($compraId, $idList);

        $cerrar = $this->postJson('/api/v1/partes/tareas/masivo/set-cerrado', [
            'accion' => 'cerrar',
            'items' => [
                ['id' => $tarea['id'], 'rowVersion' => $tarea['rowVersion']],
                ['id' => $compraId, 'rowVersion' => $compraRv],
            ],
        ], $this->authHeaders($token));
        $cerrar->assertStatus(422)->assertJsonPath('respuesta', 'partes.masivo.noEsTarea');
        $this->assertFalse((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $tarea['id'])->value('cerrado'));

        $upd = $this->postJson('/api/v1/partes/tareas/masivo/actualizar', [
            'campos' => ['sinCargo' => true],
            'items' => [
                ['id' => $tarea['id'], 'rowVersion' => $tarea['rowVersion']],
                ['id' => $compraId, 'rowVersion' => $compraRv],
            ],
        ], $this->authHeaders($token));
        $upd->assertStatus(422)->assertJsonPath('respuesta', 'partes.masivo.noEsTarea');
        $this->assertFalse((bool) DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $tarea['id'])->value('sin_cargo'));
    }

    private function encodeCurrent(int $id): string
    {
        $rv = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->value('row_version');

        return \App\Services\Partes\PartesTareaOperations::encodeRowVersion($rv);
    }
}
