<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GEN-06 ABM Seguridad (usuarios/empresas/roles/permisos) — gate `paqsuite.seguridadAdmin`.
 */
class ApiV1AdminSeguridadTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO', 'X-Company-Id' => '1'];
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

    public function test_usuarios_lookup_no_exige_gate(): void
    {
        $token = $this->loginAdmin();
        $response = $this->getJson('/api/v1/admin/usuarios', $this->authHeaders($token));
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('resultado.items'));
    }

    public function test_usuarios_crud_completo(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $create = $this->postJson('/api/v1/admin/usuarios', [
            'usuario' => 'nuevo1',
            'nombre' => 'Usuario Nuevo',
            'email' => 'nuevo1@partes.local',
            'password' => 'Secret123!',
            'activo' => true,
        ], $headers);
        $create->assertStatus(201)->assertJsonPath('resultado.item.usuario', 'nuevo1');
        $id = (int) $create->json('resultado.item.id');

        $update = $this->patchJson('/api/v1/admin/usuarios/'.$id, [
            'nombre' => 'Usuario Editado',
        ], $headers);
        $update->assertStatus(200)->assertJsonPath('resultado.item.nombre', 'Usuario Editado');

        $delete = $this->deleteJson('/api/v1/admin/usuarios/'.$id, [], $headers);
        $delete->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $id, 'inhabilitado' => true, 'activo' => false]);
    }

    public function test_empresas_solo_consulta_y_edicion(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $list = $this->getJson('/api/v1/admin/empresas', $headers);
        $list->assertStatus(200);
        $empresaId = (int) $list->json('resultado.items.0.id');
        $this->assertSame('generic.light', $list->json('resultado.items.0.theme'));

        $show = $this->getJson('/api/v1/admin/empresas/'.$empresaId, $headers);
        $show->assertStatus(200);

        $update = $this->putJson('/api/v1/admin/empresas/'.$empresaId, [
            'nombre' => 'Partes Demo Editada',
            'theme' => 'material.blue.dark',
        ], $headers);
        $update->assertStatus(200)
            ->assertJsonPath('resultado.item.nombre', 'Partes Demo Editada')
            ->assertJsonPath('resultado.item.theme', 'material.blue.dark');
    }

    public function test_empresas_theme_invalido_422(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $empresaId = (int) DB::table('pq_empresa')->value('id');

        $update = $this->putJson('/api/v1/admin/empresas/'.$empresaId, [
            'theme' => 'no-existe',
        ], $headers);
        $update->assertStatus(422);
    }

    public function test_roles_crud(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $create = $this->postJson('/api/v1/admin/roles', [
            'codigo' => 'OPERADOR',
            'nombre' => 'Operador',
            'accesoTotal' => false,
            'activo' => true,
        ], $headers);
        $create->assertStatus(201)->assertJsonPath('resultado.item.codigo', 'OPERADOR');
        $id = (int) $create->json('resultado.item.id');

        $update = $this->patchJson('/api/v1/admin/roles/'.$id, [
            'nombre' => 'Operador Editado',
        ], $headers);
        $update->assertStatus(200)->assertJsonPath('resultado.item.nombre', 'Operador Editado');

        $deleteOk = $this->deleteJson('/api/v1/admin/roles/'.$id, [], $headers);
        $deleteOk->assertStatus(200)->assertJsonPath('resultado.deleted', true);

        $create2 = $this->postJson('/api/v1/admin/roles', [
            'codigo' => 'OPERADOR2',
            'nombre' => 'Operador 2',
            'accesoTotal' => false,
            'activo' => true,
        ], $headers);
        $create2->assertStatus(201);
        $id2 = (int) $create2->json('resultado.item.id');

        $empresaId = (int) DB::table('pq_empresa')->value('id');
        $user = User::factory()->create(['usuario' => 'roldeletetest']);
        $this->postJson('/api/v1/admin/permisos', [
            'userId' => $user->id,
            'empresaId' => $empresaId,
            'rolId' => $id2,
        ], $headers)->assertStatus(201);

        $deleteBlocked = $this->deleteJson('/api/v1/admin/roles/'.$id2, [], $headers);
        $deleteBlocked->assertStatus(422)
            ->assertJsonPath('error', 1002)
            ->assertJsonPath('respuesta', 'roles.delete.hasPermisos');
    }

    public function test_permisos_crud(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $empresaId = (int) DB::table('pq_empresa')->value('id');
        $rolId = (int) DB::table('pq_roles')->where('codigo', 'SUPERVISOR')->value('id');
        $user = User::factory()->create(['usuario' => 'permisotest']);

        $create = $this->postJson('/api/v1/admin/permisos', [
            'userId' => $user->id,
            'empresaId' => $empresaId,
            'rolId' => $rolId,
        ], $headers);
        $create->assertStatus(201);
        $permisoId = (int) $create->json('resultado.item.id');

        $list = $this->getJson('/api/v1/admin/permisos', $headers);
        $list->assertStatus(200);
        $this->assertNotEmpty($list->json('resultado.items'));

        $otroRolId = (int) $this->postJson('/api/v1/admin/roles', [
            'codigo' => 'OPERADOR',
            'nombre' => 'Operador',
            'accesoTotal' => false,
            'activo' => true,
        ], $headers)->json('resultado.item.id');

        $batch = $this->postJson('/api/v1/admin/permisos/batch', [
            'items' => [
                ['userId' => $user->id, 'empresaId' => $empresaId, 'rolId' => $rolId],
                ['userId' => $user->id, 'empresaId' => $empresaId, 'rolId' => $otroRolId],
            ],
        ], $headers);
        $batch->assertStatus(200)
            ->assertJsonPath('resultado.creados', 1)
            ->assertJsonPath('resultado.omitidos', 1);

        $delete = $this->deleteJson('/api/v1/admin/permisos/'.$permisoId, [], $headers);
        $delete->assertStatus(200);
    }

    public function test_roles_atributos_get_y_put_sync(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $rolId = (int) $this->postJson('/api/v1/admin/roles', [
            'codigo' => 'CONSULTA',
            'nombre' => 'Consulta',
            'accesoTotal' => false,
            'activo' => true,
        ], $headers)->json('resultado.item.id');

        $get = $this->getJson('/api/v1/admin/roles/'.$rolId.'/atributos', $headers);
        $get->assertStatus(200)
            ->assertJsonPath('resultado.accesoTotal', false)
            ->assertJsonPath('resultado.codigo', 'CONSULTA')
            ->assertJsonPath('resultado.nombre', 'Consulta')
            ->assertJsonPath('resultado.items', []);
        $this->assertNotEmpty($get->json('resultado.arbol'));

        $menuId = (int) DB::table('pq_menus')->where('codigo', 'admin_roles')->value('id');

        $put = $this->putJson('/api/v1/admin/roles/'.$rolId.'/atributos', [
            'items' => [
                ['menuId' => $menuId, 'permisoAlta' => true, 'permisoBaja' => false, 'permisoModi' => true, 'permisoRepo' => false],
            ],
        ], $headers);
        $put->assertStatus(200);
        $put->assertJsonFragment([
            'menuId' => $menuId,
            'permisoAlta' => true,
            'permisoBaja' => false,
            'permisoModi' => true,
            'permisoRepo' => false,
        ]);

        // Reemplazo total: un segundo PUT sin ese menuId lo quita del set.
        $putEmpty = $this->putJson('/api/v1/admin/roles/'.$rolId.'/atributos', ['items' => []], $headers);
        $putEmpty->assertStatus(200)->assertJsonPath('resultado.items', []);
    }

    public function test_roles_atributos_menu_invalido_422(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $rolId = (int) DB::table('pq_roles')->insertGetId([
            'codigo' => 'CONSULTA2',
            'nombre' => 'Consulta 2',
            'acceso_total' => false,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $folderId = (int) DB::table('pq_menus')->where('codigo', 'seguridad')->value('id');

        $put = $this->putJson('/api/v1/admin/roles/'.$rolId.'/atributos', [
            'items' => [
                ['menuId' => $folderId, 'permisoAlta' => true],
            ],
        ], $headers);
        $put->assertStatus(422);

        $put2 = $this->putJson('/api/v1/admin/roles/'.$rolId.'/atributos', [
            'items' => [
                ['menuId' => 999999, 'permisoAlta' => true],
            ],
        ], $headers);
        $put2->assertStatus(422);
    }

    public function test_roles_atributos_acceso_total_422(): void
    {
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $rolId = (int) DB::table('pq_roles')->where('codigo', 'SUPERVISOR')->value('id');

        $get = $this->getJson('/api/v1/admin/roles/'.$rolId.'/atributos', $headers);
        $get->assertStatus(200)->assertJsonPath('resultado.accesoTotal', true);

        $put = $this->putJson('/api/v1/admin/roles/'.$rolId.'/atributos', ['items' => []], $headers);
        $put->assertStatus(422);
    }

    public function test_gate_rechaza_usuario_sin_acceso_total(): void
    {
        $this->seed();

        $empresaId = (int) DB::table('pq_empresa')->value('id');
        $rolId = DB::table('pq_roles')->insertGetId([
            'codigo' => 'BASICO',
            'nombre' => 'Básico',
            'acceso_total' => false,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create([
            'usuario' => 'basico1',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
        ]);
        DB::table('pq_permisos')->insert([
            'user_id' => $user->id,
            'empresa_id' => $empresaId,
            'rol_id' => $rolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $user->id,
            'code' => 'BASICO1',
            'nombre' => 'Básico Uno',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'basico1',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $login->assertStatus(200);
        $token = (string) $login->json('resultado.token');

        $response = $this->postJson('/api/v1/admin/usuarios', [
            'usuario' => 'noPermitido',
            'nombre' => 'No Permitido',
            'email' => 'no@permitido.local',
            'password' => 'Secret123!',
        ], $this->authHeaders($token));

        $response->assertStatus(403);
    }
}
