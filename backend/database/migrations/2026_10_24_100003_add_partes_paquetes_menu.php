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

        if (
            ! DB::table('pq_menus')
                ->where('id', 30300)
                ->orWhere('codigo', 'partes_carga_paquetes')
                ->exists()
            && DB::table('pq_menus')->where('id', 30000)->exists()
        ) {
            $now = now()->format('Ymd H:i:s');

            $connection = DB::connection();
            $usesSqlServer = $connection->getDriverName() === 'sqlsrv';

            if ($usesSqlServer) {
                DB::unprepared('SET IDENTITY_INSERT pq_menus ON');
            }

            try {
                DB::table('pq_menus')->insert([
                    'id' => 30300,
                    'parent_id' => 30000,
                    'codigo' => 'partes_carga_paquetes',
                    'label_key' => 'menu.partes_carga_paquetes',
                    'titulo' => 'Carga de paquetes',
                    'ruta' => '/partes/carga-paquetes',
                    'orden' => 30300,
                    'procedimiento' => 'partes_carga_paquetes',
                    'process_type' => 'A',
                    'activo' => true,
                    'enabled' => true,
                    'icon_name' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } finally {
                if ($usesSqlServer) {
                    DB::unprepared('SET IDENTITY_INSERT pq_menus OFF');
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        DB::table('pq_menus')
            ->where('id', 30300)
            ->where('codigo', 'partes_carga_paquetes')
            ->delete();
    }
};
