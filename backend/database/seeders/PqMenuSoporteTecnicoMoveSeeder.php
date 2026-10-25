<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verifica la reorganización de Soporte Técnico bajo Parámetros.
 *
 * La actualización de instalaciones existentes se ejecuta mediante la
 * migración de catálogo; este seeder evita reintroducir la jerarquía antigua
 * cuando se vuelve a ejecutar el seed completo.
 */
class PqMenuSoporteTecnicoMoveSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        $ready = DB::table('pq_menus')
            ->where('id', 60300)
            ->where('parent_id', 60000)
            ->where('codigo', 'soporte_tecnico')
            ->exists()
            && DB::table('pq_menus')
                ->where('id', 60310)
                ->where('parent_id', 60300)
                ->where('codigo', 'partes_disenador_emisiones')
                ->exists()
            && DB::table('pq_menus')
                ->where('id', 60320)
                ->where('parent_id', 60300)
                ->where('codigo', 'tareas_programadas')
                ->exists();

        if (! $ready) {
            $this->command?->warn(
                'Menú Soporte Técnico pendiente: ejecutar php artisan migrate antes de volver a seedear.',
            );
        }
    }

}
