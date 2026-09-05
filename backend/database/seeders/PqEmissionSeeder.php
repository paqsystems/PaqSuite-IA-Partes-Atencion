<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * GEN-15 (TR-011): proceso Consulta detallada + params Emission*.
 *
 * EmissionEnabled se seed/actualiza a S (adopción). Numéricos: insert-if-absent.
 * Tipo lógico GEN L no está en ParametroValorCodec 1.3.3; se persiste como S con 'S'/'N'.
 * emission.design: SUPERVISOR entra por AccesoTotal; no se asigna a CLIENTE/ASISTENTE.
 */
class PqEmissionSeeder extends Seeder
{
    public const PROCESS_CODE = 'partes.informes.consultaDetallada';

    public function run(): void
    {
        $now = now()->format('Ymd H:i:s');

        foreach (
            [
                ['EmissionEnabled', 'S', 'S', null],
                ['EmissionAsyncMaxMB', 'I', null, 5],
                ['EmissionAsyncMaxRows', 'I', null, 2000],
                ['EmissionArtifactRetentionDays', 'I', null, 30],
            ] as [$clave, $tipo, $valorString, $valorInt]
        ) {
            $exists = DB::table('pq_parametros_gral')
                ->where('programa', 'Emission')
                ->where('clave', $clave)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('pq_parametros_gral')->insert([
                'programa' => 'Emission',
                'clave' => $clave,
                'tipo_valor' => $tipo,
                'valor_string' => $valorString,
                'valor_texto' => null,
                'valor_int' => $valorInt,
                'valor_decimal' => null,
                'valor_bool' => null,
                'valor_fecha' => null,
                'precision_fecha' => null,
                'caption' => $clave,
                'tooltip' => 'GEN-15',
                'meta_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('pq_parametros_gral')
            ->where('programa', 'Emission')
            ->where('clave', 'EmissionEnabled')
            ->update([
                'valor_string' => 'S',
                'updated_at' => $now,
            ]);

        DB::table('pq_emission_processes')->updateOrInsert(
            ['process_code' => self::PROCESS_CODE],
            [
                'name' => 'Consulta detallada',
                'description' => 'Emisión del universo filtrado de Consulta detallada',
                'menu_process_code' => 'partes_consulta_detallada',
                'permite_consolidado' => true,
                'permite_segmentado' => false,
                'requiere_vista_previa' => false,
                'canal_pdf' => true,
                'canal_print' => true,
                'canal_excel' => true,
                'canal_csv' => true,
                'canal_mail' => true,
                'canal_zip' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $layout = json_encode([
            'kind' => 'tabular',
            'columns' => [
                'fecha',
                'clienteCode',
                'clienteNombre',
                'usuarioCode',
                'usuarioNombre',
                'tipoTareaCode',
                'tipoTareaDescripcion',
                'duracionMinutos',
                'sinCargo',
                'presencial',
                'cerrado',
                'observacion',
                'erpCliente',
                'erpArticulo',
            ],
        ], JSON_UNESCAPED_UNICODE);

        DB::table('pq_emission_reports')->updateOrInsert(
            [
                'process_code' => self::PROCESS_CODE,
                'report_code' => 'partes.consultaDetallada.principal',
            ],
            [
                'name' => 'Consulta detallada',
                'is_standard' => true,
                'is_principal' => true,
                'visible_mobile' => true,
                'layout_definition' => $layout,
                'layout_mime' => 'application/json',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('pq_emission_mail_templates')->updateOrInsert(
            [
                'process_code' => self::PROCESS_CODE,
                'template_code' => 'partes.consultaDetallada.mail',
            ],
            [
                'name' => 'Consulta detallada',
                'subject_template' => 'Partes — consulta detallada',
                'body_html_template' => '<p>Adjunto el reporte de consulta detallada.</p>',
                'is_principal' => true,
                'is_standard' => true,
                'visible_mobile' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
