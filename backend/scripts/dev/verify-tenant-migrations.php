<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require __DIR__ . '/../forge/bootstrap-tenant-sqlsrv.php';

$instalacion = bootstrapTenantSqlsrvFromEnv();
$db = config('database.connections.' . config('database.default') . '.database');
echo "DB={$db} cliente={$instalacion->cliente}\n";

$count = DB::selectOne('SELECT COUNT(*) AS cnt FROM migrations WITH (NOLOCK)');
echo 'migrations_total=' . ($count->cnt ?? 0) . "\n";

$rows = DB::select("SELECT migration FROM migrations WITH (NOLOCK) WHERE migration LIKE '2026_10_30%' ORDER BY migration");
foreach ($rows as $row) {
    echo 'migration: ' . $row->migration . "\n";
}

$cols = DB::select(
    "SELECT c.name FROM sys.columns c INNER JOIN sys.tables t ON c.object_id = t.object_id
     WHERE t.name = 'pq_emission_jobs' AND c.name IN ('notification_id','segment_key','artifact_path')"
);
foreach ($cols as $col) {
    echo 'column: ' . $col->name . "\n";
}
