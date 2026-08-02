<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PqPartesParametrosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->format('Ymd H:i:s');
        DB::table('pq_parametros_gral')->updateOrInsert(
            [
                'programa' => 'Partes',
                'clave' => 'PartesDuracionTramoMin',
            ],
            [
                'tipo_valor' => 'I',
                'valor_string' => null,
                'valor_texto' => null,
                'valor_int' => 15,
                'valor_decimal' => null,
                'valor_bool' => null,
                'valor_fecha' => null,
                'precision_fecha' => null,
                'caption' => 'Duración tramo (minutos)',
                'tooltip' => 'Múltiplo mínimo de duración de tareas Partes',
                'meta_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('pq_parametros_gral')->updateOrInsert(
            [
                'programa' => 'Partes',
                'clave' => 'PartesMasivoMaxIds',
            ],
            [
                'tipo_valor' => 'I',
                'valor_string' => null,
                'valor_texto' => null,
                'valor_int' => 0,
                'valor_decimal' => null,
                'valor_bool' => null,
                'valor_fecha' => null,
                'precision_fecha' => null,
                'caption' => 'Tope IDs proceso masivo',
                'tooltip' => '0 = sin tope de negocio (aplica tope técnico 5000)',
                'meta_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        foreach (
            [
                ['PartesDashboardTopN', 10, 'Top N dashboard', 'Cantidad de ítems en ranking del dashboard'],
                ['PartesDashboardRefreshSeg', 60, 'Refresh dashboard (seg)', '0 = sin auto-refresh'],
            ] as [$clave, $valor, $caption, $tooltip]
        ) {
            DB::table('pq_parametros_gral')->updateOrInsert(
                ['programa' => 'Partes', 'clave' => $clave],
                [
                    'tipo_valor' => 'I',
                    'valor_string' => null,
                    'valor_texto' => null,
                    'valor_int' => $valor,
                    'valor_decimal' => null,
                    'valor_bool' => null,
                    'valor_fecha' => null,
                    'precision_fecha' => null,
                    'caption' => $caption,
                    'tooltip' => $tooltip,
                    'meta_json' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
