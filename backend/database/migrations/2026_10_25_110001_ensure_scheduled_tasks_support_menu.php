<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        $now = now()->format('Ymd H:i:s');
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT pq_menus ON');
        }

        try {
            if (! DB::table('pq_menus')->where('codigo', 'tareas_programadas')->exists()) {
                DB::table('pq_menus')->insert([
                    'id' => 60320,
                    'parent_id' => 60300,
                    'codigo' => 'tareas_programadas',
                    'label_key' => 'menu.tareas_programadas',
                    'titulo' => 'Tareas programadas',
                    'ruta' => '/tareas-programadas',
                    'orden' => 60320,
                    'procedimiento' => 'tareas_programadas',
                    'process_type' => 'A',
                    'activo' => true,
                    'enabled' => true,
                    'icon_name' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('pq_menus')
                    ->where('codigo', 'tareas_programadas')
                    ->update([
                        'parent_id' => 60300,
                        'label_key' => 'menu.tareas_programadas',
                        'ruta' => '/tareas-programadas',
                        'orden' => 60320,
                        'updated_at' => $now,
                    ]);
            }
        } finally {
            if ($driver === 'sqlsrv') {
                DB::unprepared('SET IDENTITY_INSERT pq_menus OFF');
            }
        }
    }

    public function down(): void
    {
        // Catálogo vigente: no devolver el proceso a Seguridad.
    }
};
