<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PqExcelImportSeeder extends Seeder
{
    public const PROCESS_CODE = 'partes.tareas.import';

    public function run(): void
    {
        $now = now()->format('Ymd H:i:s');

        foreach (
            [
                ['ExcelImportEnabled', 'B', null, null, null, false],
                ['ExcelImportAsyncMaxMB', 'I', null, 5, null, null],
                ['ExcelImportAsyncMaxRows', 'I', null, 2000, null, null],
                ['ExcelImportStagingRetentionDays', 'I', null, 30, null, null],
            ] as [$clave, $tipo, $valorString, $valorInt, $valorDecimal, $valorBool]
        ) {
            $exists = DB::table('pq_parametros_gral')
                ->where('programa', 'ExcelImport')
                ->where('clave', $clave)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('pq_parametros_gral')->insert([
                'programa' => 'ExcelImport',
                'clave' => $clave,
                'tipo_valor' => $tipo,
                'valor_string' => $valorString,
                'valor_texto' => null,
                'valor_int' => $valorInt,
                'valor_decimal' => $valorDecimal,
                'valor_bool' => $valorBool,
                'valor_fecha' => null,
                'precision_fecha' => null,
                'caption' => $clave,
                'tooltip' => 'GEN-14',
                'meta_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Tests / smoke local: habilitar capacidad (prod puede dejar N y activar por ABM).
        if (app()->environment('testing')) {
            DB::table('pq_parametros_gral')
                ->where('programa', 'ExcelImport')
                ->where('clave', 'ExcelImportEnabled')
                ->update([
                    'valor_bool' => true,
                    'updated_at' => $now,
                ]);
        }

        DB::table('pq_excel_procesos')->updateOrInsert(
            ['codigo' => self::PROCESS_CODE],
            [
                'descripcion' => 'Importación de partes (tareas)',
                'menu_process_code' => 'partes_carga_diaria',
                'handler_class' => \App\Services\ExcelImport\PartesTareasImportHandler::class,
                'allow_partial' => true,
                'sheet_name' => 'Hoja1',
                'boolean_format_plantilla' => 'VERDADERO_FALSO',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('pq_excel_proceso_columnas')->where('proceso_codigo', self::PROCESS_CODE)->delete();

        // header, data_type, required, orden, help_text
        $columns = [
            ['cliente', 'string', true, 10, 'Código de cliente del maestro'],
            ['asistente', 'string', false, 20, 'Código de asistente; vacío permitido si no es supervisor'],
            ['tipo_tarea', 'string', true, 30, 'Código de tipo de tarea'],
            ['fecha', 'date', true, 40, 'Fecha de la tarea (dd/mm/yyyy)'],
            ['duracion', 'string', true, 50, 'Duración hh:mm o minutos enteros'],
            ['sin_cargo', 'bool', true, 60, 'verdadero o falso'],
            ['presencial', 'bool', true, 70, 'verdadero o falso'],
            ['descripcion', 'string', true, 80, 'Descripción de la tarea'],
        ];

        foreach ($columns as [$key, $dataType, $required, $orden, $helpText]) {
            DB::table('pq_excel_proceso_columnas')->insert([
                'proceso_codigo' => self::PROCESS_CODE,
                'column_key' => $key,
                'header' => $key,
                'caption_key' => 'partes.import.col.'.$key,
                'data_type' => $dataType,
                'is_required' => $required,
                'help_text' => $helpText,
                'decimal_places' => null,
                'orden' => $orden,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
