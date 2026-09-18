<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\PqExcelImportSeeder;
use Illuminate\Support\Facades\DB;

$central = DB::connection('paqsuite_central');
$tenants = $central->table('EMPRESAS_CONEXION')
    ->where('proyecto', 'partesatencion')
    ->where('activo', 1)
    ->orderBy('cliente')
    ->get();

$default = (string) config('database.default');

foreach ($tenants as $tenant) {
    $databaseName = (string) $tenant->database_name;
    echo "=== {$tenant->cliente} ({$databaseName}) ===\n";

    config([
        "database.connections.{$default}.database" => $databaseName,
    ]);
    DB::purge($default);

    try {
        if (! DB::getSchemaBuilder()->hasTable('pq_excel_procesos')) {
            echo "SKIP: sin tabla pq_excel_procesos\n\n";
            continue;
        }

        (new PqExcelImportSeeder())->run();

        DB::table('pq_parametros_gral')
            ->where('programa', 'ExcelImport')
            ->where('clave', 'ExcelImportEnabled')
            ->update([
                'valor_bool' => true,
                'updated_at' => now()->format('Ymd H:i:s'),
            ]);

        $count = DB::table('pq_excel_procesos')->count();
        $cols = DB::table('pq_excel_proceso_columnas')->count();
        echo "OK procesos={$count} columnas={$cols}\n\n";
    } catch (Throwable $e) {
        echo 'ERROR: ' . $e->getMessage() . "\n\n";
    }
}
