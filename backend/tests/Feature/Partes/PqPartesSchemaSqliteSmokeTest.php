<?php

namespace Tests\Feature\Partes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PqPartesSchemaSqliteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migraciones_crean_tablas_en_sqlite_y_seed_gen(): void
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

        $this->assertTrue(Schema::hasColumn('PQ_PARTES_REGISTRO_TAREA', 'row_version'));

        $this->seed(\Database\Seeders\PqPartesTiposTareaSeeder::class);
        $gen = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->first();
        $this->assertNotNull($gen);
        $this->assertTrue((bool) $gen->is_default);
        $this->assertTrue((bool) $gen->is_generico);
    }
}
