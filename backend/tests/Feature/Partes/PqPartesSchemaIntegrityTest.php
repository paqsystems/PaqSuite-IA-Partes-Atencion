<?php

namespace Tests\Feature\Partes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('sqlsrv')]
class PqPartesSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            $this->markTestSkipped('TR-001 integridad requiere SQL Server (phpunit default = sqlite).');
        }
    }

    public function test_existen_seis_tablas_pq_partes_con_columnas_clave(): void
    {
        foreach ([
            'PQ_PARTES_TIPOS_CLIENTE',
            'PQ_PARTES_TIPOS_TAREA',
            'PQ_PARTES_USUARIOS',
            'PQ_PARTES_CLIENTES',
            'PQ_PARTES_CLIENTE_TIPO_TAREA',
            'PQ_PARTES_REGISTRO_TAREA',
        ] as $tableName) {
            $this->assertTrue(Schema::hasTable($tableName), "Falta tabla {$tableName}");
        }

        $this->assertTrue(Schema::hasColumns('PQ_PARTES_REGISTRO_TAREA', [
            'usuario_id', 'cliente_id', 'tipo_tarea_id', 'fecha', 'duracion_minutos',
            'sin_cargo', 'presencial', 'observacion', 'cerrado', 'row_version',
        ]));
    }

    public function test_cliente_permite_user_id_null_y_fk_rechaza_inexistente(): void
    {
        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'T1',
            'descripcion' => 'Tipo 1',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'nombre' => 'Cliente sin acceso',
            'tipo_cliente_id' => $tipoId,
            'code' => 'CNULL',
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->assertNotNull($clienteId);

        $this->expectException(\Throwable::class);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => 999999999,
            'nombre' => 'Cliente FK fail',
            'tipo_cliente_id' => $tipoId,
            'code' => 'CFAIL',
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_trigger_rechaza_mismo_user_id_en_usuarios_y_clientes(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Exclusividad',
            'email' => 'excl-'.uniqid('', true).'@test.local',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TEX',
            'descripcion' => 'Tipo excl',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $userId,
            'code' => 'AEX',
            'nombre' => 'Asistente',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Throwable::class);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $userId,
            'nombre' => 'Cliente cruzado',
            'tipo_cliente_id' => $tipoId,
            'code' => 'CEX',
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_sp_assert_exclusividad_rechaza_cruzado(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Assert SP',
            'email' => 'assert-'.uniqid('', true).'@test.local',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $userId,
            'code' => 'ASP',
            'nombre' => 'Asistente SP',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Throwable::class);
        DB::statement('EXEC dbo.pq_sp_partes_assert_user_id_exclusividad @p_user_id = ?, @p_lado = ?', [
            $userId,
            'cliente',
        ]);
    }

    public function test_sp_marcar_default_es_atomico_y_fuerza_generico(): void
    {
        $tipoA = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
            'code' => 'ADEF',
            'descripcion' => 'A',
            'is_generico' => true,
            'is_default' => true,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoB = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
            'code' => 'BDEF',
            'descripcion' => 'B',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement('EXEC dbo.pq_sp_partes_tipos_tarea_marcar_default @p_tipo_tarea_id = ?', [$tipoB]);

        $rowA = DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $tipoA)->first();
        $rowB = DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $tipoB)->first();

        $this->assertFalse((bool) $rowA->is_default);
        $this->assertTrue((bool) $rowB->is_default);
        $this->assertTrue((bool) $rowB->is_generico);
        $this->assertSame(1, (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('is_default', true)->count());
    }

    public function test_sp_marcar_default_id_inexistente_falla(): void
    {
        $this->expectException(\Throwable::class);
        DB::statement('EXEC dbo.pq_sp_partes_tipos_tarea_marcar_default @p_tipo_tarea_id = ?', [999999999]);
    }

    public function test_seeder_deja_tipo_gen_default(): void
    {
        $this->seed(\Database\Seeders\PqPartesTiposTareaSeeder::class);
        $this->seed(\Database\Seeders\PqPartesTiposTareaSeeder::class);

        $gen = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->first();
        $this->assertNotNull($gen);
        $this->assertTrue((bool) $gen->is_generico);
        $this->assertTrue((bool) $gen->is_default);
        $this->assertSame(1, (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->count());
    }
}
