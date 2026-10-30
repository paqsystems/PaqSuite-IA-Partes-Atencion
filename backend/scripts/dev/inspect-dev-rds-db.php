<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$targetDb = $argv[1] ?? 'paqsystems_pedidosweb_dev';

echo "=== Central DEV lookup ===\n";
try {
    $rows = DB::connection('paqsuite_central')->select(
        'EXEC dbo.pq_sp_empresas_conexion_get @cliente = ?, @proyecto = ?',
        ['DEV', 'partesatencion']
    );
    foreach ($rows as $row) {
        echo '  dictionary_database=' . ($row->dictionary_database ?? $row->database_name ?? '?') . "\n";
    }
} catch (Throwable $e) {
    echo 'Central ERR: ' . $e->getMessage() . "\n";
}

echo "\n=== Inspect DB: {$targetDb} (via raw connection) ===\n";
$config = config('database.connections.sqlsrv');
$config['database'] = $targetDb;
config(['database.connections.inspect_target' => $config]);

try {
    $conn = DB::connection('inspect_target');
    $dbName = $conn->selectOne('SELECT DB_NAME() AS name');
    echo 'Connected: ' . ($dbName->name ?? '?') . "\n";

    $migs = $conn->select(
        "SELECT migration FROM migrations WITH (NOLOCK) WHERE migration LIKE '2026_10_30_140%' ORDER BY migration"
    );
    echo "Migrations 2026_10_30:\n";
    foreach ($migs as $row) {
        echo '  - ' . $row->migration . "\n";
    }
    if ($migs === []) {
        echo "  (none)\n";
    }

    $cols = $conn->select(
        "SELECT c.name FROM sys.columns c WITH (NOLOCK)
         INNER JOIN sys.tables t WITH (NOLOCK) ON c.object_id = t.object_id
         WHERE t.name = 'pq_emission_jobs'
           AND c.name IN ('notification_id','segment_key','communication_intents_json','artifact_path')
         ORDER BY c.name"
    );
    echo "pq_emission_jobs columns:\n";
    foreach ($cols as $row) {
        echo '  - ' . $row->name . "\n";
    }
    if ($cols === []) {
        echo "  (none of expected)\n";
    }

    $hasJobs = $conn->selectOne(
        "SELECT CASE WHEN OBJECT_ID(N'dbo.pq_emission_jobs', N'U') IS NULL THEN 0 ELSE 1 END AS has_table"
    );
    echo 'pq_emission_jobs exists: ' . ($hasJobs->has_table ?? '?') . "\n";

    $paramCols = $conn->select(
        "SELECT c.name, c.is_computed FROM sys.columns c WITH (NOLOCK)
         INNER JOIN sys.tables t WITH (NOLOCK) ON c.object_id = t.object_id
         WHERE t.name = 'PQ_PARAMETROS_GRAL'
           AND c.name IN ('valor_logico','valor_entero')
         ORDER BY c.name"
    );
    echo "PQ_PARAMETROS_GRAL compat columns:\n";
    foreach ($paramCols as $row) {
        echo '  - ' . $row->name . ' computed=' . $row->is_computed . "\n";
    }
    if ($paramCols === []) {
        echo "  (none)\n";
    }

    $nullMenus = $conn->selectOne(
        "SELECT COUNT(*) AS cnt FROM pq_menus WITH (NOLOCK) WHERE label_key IS NULL OR label_key = ''"
    );
    echo 'pq_menus null label_key: ' . ($nullMenus->cnt ?? '?') . "\n";
} catch (Throwable $e) {
    echo 'Inspect ERR: ' . $e->getMessage() . "\n";
}

echo "\n=== Default connection (config DB_DATABASE) ===\n";
$defaultDb = config('database.connections.sqlsrv.database');
echo 'DB_DATABASE=' . $defaultDb . "\n";
try {
    $def = DB::connection('sqlsrv');
    $m2 = $def->select(
        "SELECT migration FROM migrations WITH (NOLOCK) WHERE migration LIKE '2026_10_30_140%' ORDER BY migration"
    );
    echo "Default DB migrations 2026_10_30:\n";
    foreach ($m2 as $row) {
        echo '  - ' . $row->migration . "\n";
    }
    if ($m2 === []) {
        echo "  (none)\n";
    }
} catch (Throwable $e) {
    echo 'Default inspect ERR: ' . $e->getMessage() . "\n";
}
