<?php

namespace Tests\Unit\Partes;

use App\Services\Partes\PartesMaestrosOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** CC PQ #13 — filtros contains en Operations (sin HTTP / seed completo). */
final class PartesMaestrosOperationsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuarios_list_filtra_por_nombre(): void
    {
        $userA = DB::table('users')->insertGetId([
            'usuario' => 'u-a1',
            'name' => 'User A1',
            'email' => 'u-a1@test.local',
            'password' => bcrypt('x'),
            'activo' => 1,
            'inhabilitado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userB = DB::table('users')->insertGetId([
            'usuario' => 'u-a2',
            'name' => 'User A2',
            'email' => 'u-a2@test.local',
            'password' => bcrypt('x'),
            'activo' => 1,
            'inhabilitado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            ['user_id' => $userA, 'code' => 'A1', 'nombre' => 'ANKAS Uno', 'supervisor' => 0, 'activo' => 1, 'inhabilitado' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $userB, 'code' => 'A2', 'nombre' => 'Otro', 'supervisor' => 0, 'activo' => 1, 'inhabilitado' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $rows = PartesMaestrosOperations::dispatch('pq_sp_partes_usuarios_list', [
            'p_nombre' => 'ANKAS',
            'p_page' => 1,
            'p_page_size' => 50,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('ANKAS Uno', $rows[0]->nombre);
    }

    public function test_clientes_list_filtra_por_nombre(): void
    {
        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TC',
            'descripcion' => 'Tipo',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            ['code' => 'C1', 'nombre' => 'ANKAS SA', 'tipo_cliente_id' => $tipoId, 'activo' => true, 'inhabilitado' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'C2', 'nombre' => 'Otro SA', 'tipo_cliente_id' => $tipoId, 'activo' => true, 'inhabilitado' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $rows = PartesMaestrosOperations::dispatch('pq_sp_partes_clientes_list', [
            'p_nombre' => 'ANKAS',
            'p_page' => 1,
            'p_page_size' => 50,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('ANKAS SA', $rows[0]->nombre);
    }

    public function test_tipos_cliente_list_filtra_por_descripcion(): void
    {
        DB::table('PQ_PARTES_TIPOS_CLIENTE')->insert([
            ['code' => 'TC1', 'descripcion' => 'Premium ANKAS', 'activo' => true, 'inhabilitado' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'TC2', 'descripcion' => 'Standard', 'activo' => true, 'inhabilitado' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $rows = PartesMaestrosOperations::dispatch('pq_sp_partes_tipos_cliente_list', [
            'p_descripcion' => 'ANKAS',
            'p_page' => 1,
            'p_page_size' => 50,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('Premium ANKAS', $rows[0]->descripcion);
    }

    public function test_tipos_tarea_list_filtra_por_descripcion(): void
    {
        DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
            ['code' => 'TT1', 'descripcion' => 'Soporte ANKAS', 'is_generico' => false, 'is_default' => false, 'activo' => true, 'inhabilitado' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'TT2', 'descripcion' => 'Dev', 'is_generico' => false, 'is_default' => false, 'activo' => true, 'inhabilitado' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $rows = PartesMaestrosOperations::dispatch('pq_sp_partes_tipos_tarea_list', [
            'p_descripcion' => 'ANKAS',
            'p_page' => 1,
            'p_page_size' => 50,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('Soporte ANKAS', $rows[0]->descripcion);
    }
}
