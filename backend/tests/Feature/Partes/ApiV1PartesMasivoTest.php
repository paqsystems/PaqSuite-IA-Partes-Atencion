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

    private function encodeCurrent(int $id): string
    {
        $rv = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->value('row_version');

        return \App\Services\Partes\PartesTareaOperations::encodeRowVersion($rv);
    }
}
