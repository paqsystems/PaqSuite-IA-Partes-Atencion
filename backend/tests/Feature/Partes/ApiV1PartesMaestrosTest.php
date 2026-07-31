<?php

namespace Tests\Feature\Partes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiV1PartesMaestrosTest extends TestCase
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

    public function test_lookup_usuarios_solo_activos(): void
    {
        $token = $this->loginAdmin();
        $response = $this->getJson('/api/v1/admin/usuarios?soloActivos=1', $this->authHeaders($token));
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('resultado.items'));
    }

    public function test_crud_asistente_y_exclusividad(): void
    {
        $token = $this->loginAdmin();
        $user = User::factory()->create([
            'usuario' => 'asst2',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
        ]);

        $create = $this->postJson('/api/v1/partes/asistentes', [
            'userId' => $user->id,
            'code' => 'A002',
            'nombre' => 'Asistente Dos',
            'supervisor' => false,
        ], $this->authHeaders($token));
        $create->assertStatus(201)->assertJsonPath('resultado.item.code', 'A002');

        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCX',
            'descripcion' => 'X',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clienteFail = $this->postJson('/api/v1/partes/clientes', [
            'userId' => $user->id,
            'code' => 'CX1',
            'nombre' => 'Cruzado',
            'tipoClienteId' => $tipoId,
        ], $this->authHeaders($token));
        $clienteFail->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.maestros.exclusividadUserId');
    }

    public function test_tipo_default_atomico_y_no_inhabilitar(): void
    {
        $token = $this->loginAdmin();
        $create = $this->postJson('/api/v1/partes/tipos-tarea', [
            'code' => 'OTRO',
            'descripcion' => 'Otro',
            'isGenerico' => false,
            'isDefault' => true,
        ], $this->authHeaders($token));
        $create->assertStatus(201);
        $id = (int) $create->json('resultado.item.id');
        $this->assertTrue((bool) $create->json('resultado.item.isDefault'));
        $this->assertTrue((bool) $create->json('resultado.item.isGenerico'));
        $this->assertSame(1, (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('is_default', true)->count());

        $inh = $this->patchJson("/api/v1/partes/tipos-tarea/{$id}/estado", [
            'inhabilitado' => true,
        ], $this->authHeaders($token));
        $inh->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.maestros.tipoDefaultNoInhabilitar');
    }

    public function test_asignacion_rechaza_generico_y_universo_requiere_cliente(): void
    {
        $token = $this->loginAdmin();
        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCY',
            'descripcion' => 'Y',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'nombre' => 'Cli',
            'tipo_cliente_id' => $tipoId,
            'code' => 'CLY',
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $genId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');

        $asig = $this->postJson('/api/v1/partes/cliente-tipos-tarea', [
            'clienteId' => $clienteId,
            'tipoTareaId' => $genId,
        ], $this->authHeaders($token));
        $asig->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.maestros.tipoGenericoNoAsignable');

        $universo = $this->getJson('/api/v1/partes/catalogos/tipos-tarea', $this->authHeaders($token));
        $universo->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.maestros.clienteIdRequired');

        $ok = $this->getJson('/api/v1/partes/catalogos/tipos-tarea?clienteId='.$clienteId, $this->authHeaders($token));
        $ok->assertStatus(200);
        $codes = collect($ok->json('resultado.items'))->pluck('code');
        $this->assertTrue($codes->contains('GEN'));
    }

    public function test_cliente_funcional_forbidden_en_maestros(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'usuario' => 'cliuser',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCC',
            'descripcion' => 'C',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $user->id,
            'nombre' => 'Org',
            'tipo_cliente_id' => $tipoId,
            'code' => 'ORG1',
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'cliuser',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $token = $login->json('resultado.token');

        $this->getJson('/api/v1/partes/asistentes', $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'partes.maestros.forbidden');
    }
}
