<?php

declare(strict_types=1);

/**
 * Forge GEN-34 — un solo proceso PHP: tenant DB + migrate + deploy-sp + installation:update.
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
/** @var Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

require __DIR__ . '/bootstrap-tenant-sqlsrv.php';

try {
    $instalacion = bootstrapTenantSqlsrvFromEnv();
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$defaultConnection = (string) config('database.default');
$databaseName = (string) config("database.connections.{$defaultConnection}.database");
echo "Post-deploy tenant {$instalacion->cliente}/{$instalacion->proyecto} → {$databaseName}\n";

$migrateExit = $kernel->call('migrate', ['--force' => true]);
if ($migrateExit !== 0) {
    exit($migrateExit);
}

define('PAQSUITE_DEPLOY_SP_NO_AUTO_RUN', true);
require __DIR__ . '/../deploy-sp.php';
deployHostStoredProcedures();

$artisanCommands = $kernel->all();
if (array_key_exists('paqsuite:installation:update', $artisanCommands)) {
    $updateExit = $kernel->call('paqsuite:installation:update', [
        '--cliente' => $instalacion->cliente,
        '--proyecto' => $instalacion->proyecto,
        '--non-interactive' => true,
    ]);
    if ($updateExit !== 0) {
        exit($updateExit);
    }
} else {
    echo "paqsuite:installation:update no disponible; omito GEN-34 CLI.\n";
}

exit(0);
