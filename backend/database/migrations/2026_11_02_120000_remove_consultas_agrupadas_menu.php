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

        DB::table('pq_menus')
            ->where('id', 40200)
            ->orWhere('codigo', 'partes_consultas_agrupadas')
            ->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        if (DB::table('pq_menus')->where('id', 40200)->exists()) {
            return;
        }

        if (! DB::table('pq_menus')->where('id', 40000)->exists()) {
            return;
        }

        $now = now()->format('Ymd H:i:s');
        $usesSqlServer = DB::connection()->getDriverName() === 'sqlsrv';

        if ($usesSqlServer) {
            DB::unprepared('SET IDENTITY_INSERT pq_menus ON');
        }

        try {
            DB::table('pq_menus')->insert([
                'id' => 40200,
                'parent_id' => 40000,
                'codigo' => 'partes_consultas_agrupadas',
                'label_key' => 'menu.partes_consultas_agrupadas',
                'titulo' => 'Consultas agrupadas',
                'ruta' => '/partes/informes/consultas-agrupadas',
                'orden' => 40200,
                'procedimiento' => 'partes_consultas_agrupadas',
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
};
