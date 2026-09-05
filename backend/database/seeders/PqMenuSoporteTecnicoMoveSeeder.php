<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotente para BD ya seedadas: carpeta Soporte Técnico (70000)
 * y diseñador (70100). No borra el resto del menú.
 */
class PqMenuSoporteTecnicoMoveSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->format('Ymd H:i:s');
        $driver = Schema::getConnection()->getDriverName();

        $this->withIdentityInsert($driver, function () use ($now): void {
            if (! DB::table('pq_menus')->where('id', 70000)->exists()) {
                DB::table('pq_menus')->insert($this->folderRow(70000, $now));
            } else {
                DB::table('pq_menus')->where('id', 70000)->update([
                    'codigo' => 'soporte_tecnico',
                    'titulo' => 'Soporte Técnico',
                    'parent_id' => null,
                    'updated_at' => $now,
                ]);
            }

            $designer = DB::table('pq_menus')->where('codigo', 'partes_disenador_emisiones')->first();
            if ($designer === null) {
                $legacy = DB::table('pq_menus')->where('id', 60300)->first();
                $designer = $legacy;
            }

            if ($designer === null) {
                DB::table('pq_menus')->insert($this->designerRow(70100, $now));

                return;
            }

            $currentId = (int) $designer->id;
            if ($currentId === 70100) {
                DB::table('pq_menus')->where('id', 70100)->update([
                    'parent_id' => 70000,
                    'codigo' => 'partes_disenador_emisiones',
                    'titulo' => (string) ($designer->titulo ?: 'Diseñador de emisiones'),
                    'ruta' => '/emisiones/disenador',
                    'orden' => 70100,
                    'updated_at' => $now,
                ]);

                return;
            }

            Schema::disableForeignKeyConstraints();
            try {
                DB::table('pq_menus')->where('id', $currentId)->update([
                    'codigo' => 'partes_disenador_emisiones_old',
                    'updated_at' => $now,
                ]);

                if (! DB::table('pq_menus')->where('id', 70100)->exists()) {
                    DB::table('pq_menus')->insert($this->designerRow(70100, $now, $designer));
                }

                if (Schema::hasTable('pq_rol_atributos')) {
                    DB::table('pq_rol_atributos')->where('menu_id', $currentId)->update(['menu_id' => 70100]);
                }

                DB::table('pq_menus')->where('id', $currentId)->delete();
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });
    }

    /**
     * @param  object|null  $source
     * @return array<string, mixed>
     */
    private function designerRow(int $id, string $now, ?object $source = null): array
    {
        return [
            'id' => $id,
            'parent_id' => 70000,
            'codigo' => 'partes_disenador_emisiones',
            'titulo' => (string) ($source->titulo ?? 'Diseñador de emisiones'),
            'ruta' => (string) ($source->ruta ?? '/emisiones/disenador'),
            'orden' => 70100,
            'procedimiento' => (string) ($source->procedimiento ?? 'partes_disenador_emisiones'),
            'process_type' => (string) ($source->process_type ?? 'A'),
            'activo' => true,
            'enabled' => true,
            'icon_name' => $source->icon_name ?? null,
            'created_at' => $source->created_at ?? $now,
            'updated_at' => $now,
        ];
    }

    /** @return array<string, mixed> */
    private function folderRow(int $id, string $now): array
    {
        return [
            'id' => $id,
            'parent_id' => null,
            'codigo' => 'soporte_tecnico',
            'titulo' => 'Soporte Técnico',
            'ruta' => null,
            'orden' => $id,
            'procedimiento' => 'soporte_tecnico',
            'process_type' => 'F',
            'activo' => true,
            'enabled' => true,
            'icon_name' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function withIdentityInsert(string $driver, callable $callback): void
    {
        if ($driver === 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT pq_menus ON');
        }
        try {
            $callback();
        } finally {
            if ($driver === 'sqlsrv') {
                DB::unprepared('SET IDENTITY_INSERT pq_menus OFF');
            }
        }
    }
}
