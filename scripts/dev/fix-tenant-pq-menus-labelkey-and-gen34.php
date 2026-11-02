<?php

declare(strict_types=1);

/**
 * Corrige NULL/vacío en pq_menus.labelKey (y label_key) antes de pq_menus_labelKey_not_null_pilot
 * del manifiesto GEN-34, luego migrate + installation:update + deploy-sp del host.
 *
 * Uso (desde backend/ con .env apuntando a RDS):
 *   php ../scripts/dev/fix-tenant-pq-menus-labelkey-and-gen34.php --cliente=ESTUDIOGB
 *   php ../scripts/dev/fix-tenant-pq-menus-labelkey-and-gen34.php --cliente=PAQ
 *   php ../scripts/dev/fix-tenant-pq-menus-labelkey-and-gen34.php --database=paqsystems_partesatencion_estudiogb --cliente=ESTUDIOGB
 */

require __DIR__ . '/../../backend/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\BufferedOutput;

$app = require __DIR__ . '/../../backend/bootstrap/app.php';
/** @var ConsoleKernel $kernel */
$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

require_once __DIR__ . '/../../backend/scripts/forge/post-deploy-installations.php';

function readCliOption(string $name): ?string
{
    $prefix = '--'.$name.'=';
    global $argv;
    foreach ($argv as $arg) {
        if (str_starts_with($arg, $prefix)) {
            return trim(substr($arg, strlen($prefix)));
        }
    }

    return null;
}

$cliente = readCliOption('cliente');
$proyecto = readCliOption('proyecto') ?? trim(getenv('PAQSUITE_PROYECTO') ?: 'partesatencion');
$databaseOverride = readCliOption('database');
$skipGen34 = in_array('--skip-gen34', $argv, true);

if ($cliente === null || $cliente === '') {
    fwrite(STDERR, "Uso: php fix-tenant-pq-menus-labelkey-and-gen34.php --cliente=ESTUDIOGB [--proyecto=partesatencion] [--database=...] [--skip-gen34]\n");
    exit(1);
}

try {
    $instalacion = bootstrapTenantSqlsrvForInstallation($cliente, $proyecto);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage()."\n");
    exit(1);
}

if ($databaseOverride !== null && $databaseOverride !== '') {
    config(['database.connections.sqlsrv.database' => $databaseOverride]);
    DB::purge('sqlsrv');
    DB::reconnect('sqlsrv');
}

$dbName = (string) config('database.connections.sqlsrv.database');
echo "=== Backfill pq_menus labelKey → {$cliente}/{$proyecto} ({$dbName}) ===\n";

if (! Schema::hasTable('pq_menus')) {
    fwrite(STDERR, "Tabla pq_menus ausente.\n");
    exit(1);
}

$hasLabelKey = Schema::hasColumn('pq_menus', 'labelKey');
$hasLabelKeySnake = Schema::hasColumn('pq_menus', 'label_key');

if (! $hasLabelKey && ! $hasLabelKeySnake) {
    echo "Sin columnas labelKey/label_key; nada que backfillear.\n";
} else {
    $rows = DB::table('pq_menus')->select(['id', 'codigo', 'label_key', 'labelKey'])->get();
    $updated = 0;
    foreach ($rows as $row) {
        $codigo = trim((string) ($row->codigo ?? ''));
        $fallback = $codigo !== '' ? 'menu.'.$codigo : 'menu.id.'.$row->id;

        $patch = [];
        if ($hasLabelKeySnake) {
            $lk = $row->label_key;
            if ($lk === null || trim((string) $lk) === '') {
                $patch['label_key'] = $fallback;
            }
        }
        if ($hasLabelKey) {
            $legacy = $row->labelKey;
            if ($legacy === null || trim((string) $legacy) === '') {
                $fromSnake = $patch['label_key'] ?? $row->label_key;
                $patch['labelKey'] = ($fromSnake !== null && trim((string) $fromSnake) !== '')
                    ? $fromSnake
                    : $fallback;
            }
        }
        if ($patch !== []) {
            DB::table('pq_menus')->where('id', $row->id)->update($patch);
            $updated++;
        }
    }
    echo "Filas actualizadas: {$updated}\n";

    if ($hasLabelKey) {
        $nullCount = DB::table('pq_menus')
            ->whereNull('labelKey')
            ->orWhere('labelKey', '')
            ->count();
        echo "labelKey NULL/vacío restante: {$nullCount}\n";
        if ($nullCount > 0) {
            fwrite(STDERR, "Quedan NULL; revisar pq_menus manualmente antes del NOT NULL del manifiesto.\n");
            exit(1);
        }
    }
}

if ($skipGen34) {
    echo "(--skip-gen34) Fin.\n";
    exit(0);
}

echo "\n=== migrate --force ===\n";
if ($kernel->call('migrate', ['--force' => true]) !== 0) {
    exit(1);
}

if (! array_key_exists('paqsuite:installation:update', $kernel->all())) {
    fwrite(STDERR, "paqsuite:installation:update no disponible.\n");
    exit(1);
}

echo "\n=== paqsuite:installation:update ===\n";
$output = new BufferedOutput();
$updateExit = $kernel->call('paqsuite:installation:update', [
    '--cliente' => $instalacion->cliente,
    '--proyecto' => $instalacion->proyecto,
    '--non-interactive' => true,
], $output);
echo trim($output->fetch())."\n";
if ($updateExit !== 0) {
    exit($updateExit);
}

define('PAQSUITE_DEPLOY_SP_NO_AUTO_RUN', true);
require __DIR__ . '/../../backend/scripts/deploy-sp.php';
deployHostStoredProcedures();

echo "\nOK {$cliente}/{$proyecto}\n";
exit(0);
