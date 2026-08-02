<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PqPartesTiposTareaSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('PQ_PARTES_TIPOS_TAREA')) {
            return;
        }

        $now = now();
        $existing = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->first();

        if ($existing === null) {
            DB::table('PQ_PARTES_TIPOS_TAREA')->where('is_default', true)->update([
                'is_default' => false,
                'updated_at' => $now,
            ]);

            DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
                'code' => 'GEN',
                'descripcion' => 'General',
                'is_generico' => true,
                'is_default' => true,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        DB::table('PQ_PARTES_TIPOS_TAREA')->where('is_default', true)->where('id', '<>', $existing->id)->update([
            'is_default' => false,
            'updated_at' => $now,
        ]);

        DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $existing->id)->update([
            'descripcion' => 'General',
            'is_generico' => true,
            'is_default' => true,
            'activo' => true,
            'inhabilitado' => false,
            'updated_at' => $now,
        ]);
    }
}
