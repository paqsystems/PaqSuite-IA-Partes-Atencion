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

        $driver = DB::connection()->getDriverName();
        $now = now()->format('Ymd H:i:s');

        Schema::disableForeignKeyConstraints();
        if ($driver === 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT pq_menus ON');
        }

        try {
            $this->ensureSupportFolder($now);
            $this->moveMenuNode(
                'partes_disenador_emisiones',
                60310,
                60300,
                'Diseñador de emisiones',
                '/emisiones/disenador',
                $now,
            );
            $this->moveMenuNode(
                'tareas_programadas',
                60320,
                60300,
                'Tareas programadas',
                '/tareas-programadas',
                $now,
            );

            DB::table('pq_menus')
                ->whereIn('id', [70000])
                ->whereIn('codigo', ['soporte_tecnico', 'soporte_tecnico_legacy'])
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('pq_menus as children')
                        ->whereColumn('children.parent_id', 'pq_menus.id');
                })
                ->delete();
        } finally {
            if ($driver === 'sqlsrv') {
                DB::unprepared('SET IDENTITY_INSERT pq_menus OFF');
            }
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // La reorganización es una corrección de catálogo; no se revierte para
        // evitar devolver procesos a una jerarquía que ya no es la vigente.
    }

    private function ensureSupportFolder(string $now): void
    {
        $folder = DB::table('pq_menus')
            ->where('codigo', 'soporte_tecnico')
            ->orderByRaw('CASE WHEN id = 60300 THEN 0 ELSE 1 END')
            ->first();

        $target = DB::table('pq_menus')->where('id', 60300)->first();
        if ($target !== null && $target->codigo !== 'soporte_tecnico') {
            if ($target->codigo === 'partes_disenador_emisiones') {
                $designerTarget = DB::table('pq_menus')->where('id', 60310)->first();
                if ($designerTarget === null) {
                    $designer = get_object_vars($target);
                    $designer['id'] = 60310;
                    $designer['parent_id'] = 60300;
                    $designer['orden'] = 60310;
                    $designer['updated_at'] = $now;
                    DB::table('pq_menus')->where('id', 60300)->update([
                        'codigo' => 'partes_disenador_emisiones_legacy',
                        'updated_at' => $now,
                    ]);
                    DB::table('pq_menus')->insert($designer);
                }
                $this->moveRoleAttributes(60300, 60310);
                DB::table('pq_menus')->where('id', 60300)->delete();
            } else {
                DB::table('pq_menus')->where('id', 60300)->update([
                    'codigo' => 'soporte_tecnico_legacy',
                    'updated_at' => $now,
                ]);
            }
            $target = null;
        }

        if ($target === null) {
            $source = $folder;
            if ($source !== null && (int) $source->id !== 60300) {
                DB::table('pq_menus')->where('codigo', 'soporte_tecnico')->update([
                    'codigo' => 'soporte_tecnico_legacy',
                    'updated_at' => $now,
                ]);
            }
            DB::table('pq_menus')->insert([
                'id' => 60300,
                'parent_id' => 60000,
                'codigo' => 'soporte_tecnico',
                'label_key' => 'menu.soporte_tecnico',
                'titulo' => 'Soporte Técnico',
                'ruta' => null,
                'orden' => 60300,
                'procedimiento' => 'soporte_tecnico',
                'process_type' => 'F',
                'activo' => true,
                'enabled' => true,
                'icon_name' => $source?->icon_name,
                'created_at' => $source?->created_at ?? $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('pq_menus')->where('id', 60300)->update([
                'parent_id' => 60000,
                'codigo' => 'soporte_tecnico',
                'label_key' => 'menu.soporte_tecnico',
                'titulo' => 'Soporte Técnico',
                'ruta' => null,
                'orden' => 60300,
                'procedimiento' => 'soporte_tecnico',
                'process_type' => 'F',
                'activo' => true,
                'enabled' => true,
                'updated_at' => $now,
            ]);
        }
    }

    private function moveMenuNode(
        string $codigo,
        int $targetId,
        int $parentId,
        string $title,
        string $route,
        string $now,
    ): void
    {
        $source = DB::table('pq_menus')->where('codigo', $codigo)->first();
        $target = DB::table('pq_menus')->where('id', $targetId)->first();

        if ($source === null && $target === null) {
            DB::table('pq_menus')->insert([
                'id' => $targetId,
                'parent_id' => $parentId,
                'codigo' => $codigo,
                'label_key' => 'menu.'.$codigo,
                'titulo' => $title,
                'ruta' => $route,
                'orden' => $targetId,
                'procedimiento' => $codigo,
                'process_type' => 'A',
                'activo' => true,
                'enabled' => true,
                'icon_name' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        if ($target === null) {
            $attributes = get_object_vars($source);
            $attributes['id'] = $targetId;
            $attributes['parent_id'] = $parentId;
            $attributes['orden'] = $targetId;
            $attributes['updated_at'] = $now;
            DB::table('pq_menus')->where('id', $source->id)->update([
                'codigo' => $codigo.'_legacy',
                'updated_at' => $now,
            ]);
            DB::table('pq_menus')->insert($attributes);
            $target = DB::table('pq_menus')->where('id', $targetId)->first();
        } else {
            DB::table('pq_menus')->where('id', $targetId)->update([
                'parent_id' => $parentId,
                'codigo' => $codigo,
                'label_key' => 'menu.'.$codigo,
                'ruta' => $source?->ruta ?? $target->ruta,
                'orden' => $targetId,
                'procedimiento' => $source?->procedimiento ?? $target->procedimiento,
                'process_type' => $source?->process_type ?? $target->process_type,
                'activo' => true,
                'enabled' => true,
                'updated_at' => $now,
            ]);
        }

        if ($source !== null && (int) $source->id !== $targetId) {
            $this->moveRoleAttributes((int) $source->id, $targetId);
            DB::table('pq_menus')->where('id', $source->id)->delete();
        }
    }

    private function moveRoleAttributes(int $sourceMenuId, int $targetMenuId): void
    {
        if (! Schema::hasTable('pq_rol_atributos')) {
            return;
        }

        $attributes = DB::table('pq_rol_atributos')
            ->where('menu_id', $sourceMenuId)
            ->get();

        foreach ($attributes as $attribute) {
            $values = get_object_vars($attribute);
            unset($values['id'], $values['rol_id'], $values['menu_id']);
            DB::table('pq_rol_atributos')->updateOrInsert(
                [
                    'rol_id' => $attribute->rol_id,
                    'menu_id' => $targetMenuId,
                ],
                $values,
            );
        }

        DB::table('pq_rol_atributos')->where('menu_id', $sourceMenuId)->delete();
    }
};
