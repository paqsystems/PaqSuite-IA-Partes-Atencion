<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo `pq_menus` con id jerárquico (regla BASE 76 / SPEC-001-07 §1.2).
 * Raíces = múltiplos de 10 000; hijos en el bloque del padre.
 */
class PqMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->format('Ymd H:i:s');
        $driver = Schema::getConnection()->getDriverName();

        Schema::disableForeignKeyConstraints();
        DB::table('pq_menus')->delete();

        if ($driver === 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT pq_menus ON');
        }

        $rows = [
            // 10000 Inicio
            $this->folder(10000, null, 'inicio', 'Inicio', 10000),
            $this->process(10100, 10000, 'partes_dashboard', 'Dashboard', '/partes', 10100),

            // 20000 Archivos
            $this->folder(20000, null, 'archivos', 'Archivos', 20000),
            $this->process(20100, 20000, 'partes_asistentes', 'Asistentes', '/archivos/partes/asistentes', 20100),
            $this->process(20200, 20000, 'partes_clientes', 'Clientes', '/archivos/partes/clientes', 20200),
            $this->process(20300, 20000, 'partes_tipos_cliente', 'Tipos de cliente', '/archivos/partes/tipos-cliente', 20300),
            $this->process(20400, 20000, 'partes_tipos_tarea', 'Tipos de tarea', '/archivos/partes/tipos-tarea', 20400),
            $this->process(20500, 20000, 'partes_cliente_tipo_tarea', 'Asignación tipos por cliente', '/archivos/partes/cliente-tipos-tarea', 20500),

            // 30000 Partes (operación)
            $this->folder(30000, null, 'partes', 'Partes', 30000),
            $this->process(30100, 30000, 'partes_carga_diaria', 'Carga diaria', '/partes/carga-diaria', 30100),
            $this->process(30200, 30000, 'partes_proceso_masivo', 'Proceso masivo', '/partes/proceso-masivo', 30200),

            // 40000 Informes
            $this->folder(40000, null, 'informes', 'Informes', 40000),
            $this->process(40100, 40000, 'partes_consulta_detallada', 'Consulta detallada', '/partes/informes/consulta-detallada', 40100),
            $this->process(40200, 40000, 'partes_consultas_agrupadas', 'Consultas agrupadas', '/partes/informes/consultas-agrupadas', 40200),
            $this->process(40300, 40000, 'partes_informe_paquete_horas', 'Paquete de horas', '/partes/informes/paquete-horas', 40300),

            // 50000 Seguridad (GEN)
            $this->folder(50000, null, 'seguridad', 'Seguridad', 50000),
            $this->process(50100, 50000, 'admin_usuarios', 'Usuarios', '/admin/usuarios', 50100),
            $this->process(50200, 50000, 'admin_roles', 'Roles', '/admin/roles', 50200),
            $this->process(50300, 50000, 'admin_permisos', 'Permisos', '/admin/permisos', 50300),
            $this->process(50400, 50000, 'admin_empresas', 'Empresas', '/admin/empresas', 50400),

            // 60000 Parámetros (Auth Framework + Partes)
            $this->folder(60000, null, 'parametros', 'Parámetros', 60000),
            $this->process(60100, 60000, 'parametros_auth', 'Parámetros Auth', '/parametros/Auth', 60100),
            $this->process(60200, 60000, 'parametros_partes', 'Parámetros Partes', '/parametros/Partes', 60200),

            // 70000 Soporte Técnico
            $this->folder(70000, null, 'soporte_tecnico', 'Soporte Técnico', 70000),
            $this->process(70100, 70000, 'partes_disenador_emisiones', 'Diseñador de emisiones', '/emisiones/disenador', 70100),
        ];

        foreach ($rows as $row) {
            DB::table('pq_menus')->insert(array_merge($row, [
                'activo' => true,
                'enabled' => true,
                'icon_name' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        if ($driver === 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT pq_menus OFF');
        }

        Schema::enableForeignKeyConstraints();
    }

    /** @return array<string, mixed> */
    private function folder(int $id, ?int $parentId, string $codigo, string $titulo, int $orden): array
    {
        return [
            'id' => $id,
            'parent_id' => $parentId,
            'codigo' => $codigo,
            'titulo' => $titulo,
            'ruta' => null,
            'orden' => $orden,
            'procedimiento' => $codigo,
            'process_type' => 'F',
        ];
    }

    /** @return array<string, mixed> */
    private function process(
        int $id,
        int $parentId,
        string $codigo,
        string $titulo,
        string $ruta,
        int $orden
    ): array {
        return [
            'id' => $id,
            'parent_id' => $parentId,
            'codigo' => $codigo,
            'titulo' => $titulo,
            'ruta' => $ruta,
            'orden' => $orden,
            'procedimiento' => $codigo,
            'process_type' => 'A',
        ];
    }
}
