<?php

use Illuminate\Support\Facades\DB;

function deploySpNormalizeSqlBatch(string $batch): string
{
    $lines = preg_split('/\R/', $batch) ?: [];
    $kept = [];
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if ($trim === '' || str_starts_with($trim, '--')) {
            continue;
        }
        $kept[] = $line;
    }

    return trim(implode("\n", $kept));
}

function deploySpRunSqlFile(string $connection, string $path): void
{
    if (! is_file($path)) {
        echo "SKIP missing: $path\n";
        return;
    }

    $sql = file_get_contents($path);
    $batches = preg_split('/^\s*GO\s*$/mi', $sql) ?: [];
    $conn = DB::connection($connection);
    $ok = 0;
    foreach ($batches as $batch) {
        $batch = deploySpNormalizeSqlBatch($batch);
        if ($batch === '') {
            continue;
        }
        try {
            $conn->unprepared($batch);
            $ok++;
        } catch (Throwable $e) {
            echo 'ERROR in ' . basename($path) . ': ' . $e->getMessage() . "\n";
            echo substr($batch, 0, 200) . "...\n";
            throw $e;
        }
    }
    echo 'OK ' . basename($path) . " ($ok batches)\n";
}

function deployHostStoredProcedures(): void
{
    // SP GEN (tasks, emission, excel, …) se despliegan vía paqsuite:installation:update (manifiesto Framework).
    $hostSpFiles = glob(__DIR__ . '/../database/sp/*.sql') ?: [];
    sort($hostSpFiles);
    $companyFiles = $hostSpFiles;

    $seenBasenames = [];
    echo '=== Company DB (' . config('database.default') . ") ===\n";
    foreach ($companyFiles as $file) {
        $base = basename($file);
        if (isset($seenBasenames[$base])) {
            continue;
        }
        $seenBasenames[$base] = true;
        if (in_array($base, ['ensure_paqsystems_instalacion_partesatencion.sql', 'seed_empresas_conexion_partesatencion_demo.sql'], true)) {
            continue;
        }
        deploySpRunSqlFile(config('database.default'), $file);
    }

    echo "=== Central PAQSYSTEMS ===\n";
    try {
        $centralFile = __DIR__ . '/../database/sp/ensure_paqsystems_instalacion_partesatencion.sql';
        $pass = (string) config('database.connections.sqlsrv.password');
        $sql = file_get_contents($centralFile);
        $sql = str_replace("DECLARE @pass NVARCHAR(MAX) = N'';", "DECLARE @pass NVARCHAR(MAX) = N'" . str_replace("'", "''", $pass) . "';", $sql);
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ensure_paqsystems_partes.sql';
        file_put_contents($tmp, $sql);
        deploySpRunSqlFile('paqsuite_central', $tmp);
        @unlink($tmp);
    } catch (Throwable $e) {
        echo 'Central skipped/error: ' . $e->getMessage() . "\n";
    }

    echo "Done.\n";
}

if (! defined('PAQSUITE_DEPLOY_SP_NO_AUTO_RUN')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    if (trim(getenv('PAQSUITE_CLIENTE') ?: '') !== '') {
        require __DIR__ . '/forge/bootstrap-tenant-sqlsrv.php';
        bootstrapTenantSqlsrvFromEnv();
    }

    deployHostStoredProcedures();
}
