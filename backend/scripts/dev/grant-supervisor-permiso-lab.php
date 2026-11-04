<?php

declare(strict_types=1);

/**
 * Lab: asigna rol SUPERVISOR (acceso total → emission.design) a un usuario vía SP.
 * Uso: php scripts/dev/grant-supervisor-permiso-lab.php ST
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require __DIR__ . '/../forge/bootstrap-tenant-sqlsrv.php';

use App\Repositories\Sp\SpAccesoTotalChecker;
use App\Repositories\Sp\SpPermisoAdminRepository;
use Illuminate\Support\Facades\DB;

$login = trim($argv[1] ?? '');
if ($login === '') {
    fwrite(STDERR, "Uso: php scripts/dev/grant-supervisor-permiso-lab.php <usuario>\n");
    exit(1);
}

bootstrapTenantSqlsrvFromEnv();

$user = DB::table('users')
    ->whereRaw('UPPER(usuario) = ?', [strtoupper($login)])
    ->first();

if ($user === null) {
    fwrite(STDERR, "Usuario no encontrado: {$login}\n");
    exit(1);
}

$rolId = DB::table('pq_roles')->where('codigo', 'SUPERVISOR')->where('activo', 1)->value('id');
if ($rolId === null) {
    fwrite(STDERR, "Rol SUPERVISOR no encontrado.\n");
    exit(1);
}

$empresaId = DB::table('pq_permisos')->where('user_id', $user->id)->value('empresa_id');
if ($empresaId === null) {
    $empresaId = DB::table('pq_empresa')->where('activo', 1)->orderBy('id')->value('id');
}
if ($empresaId === null) {
    fwrite(STDERR, "No hay empresa activa para asignar permiso.\n");
    exit(1);
}

/** @var SpPermisoAdminRepository $permisos */
$permisos = app(SpPermisoAdminRepository::class);
$batch = $permisos->createBatch([
    [
        'usuarioId' => (int) $user->id,
        'empresaId' => (int) $empresaId,
        'rolId' => (int) $rolId,
    ],
]);

/** @var SpAccesoTotalChecker $acceso */
$acceso = app(SpAccesoTotalChecker::class);
$hasAcceso = $acceso->hasAccesoTotal((int) $user->id, (int) $empresaId);

echo json_encode([
    'usuario' => $user->usuario,
    'userId' => (int) $user->id,
    'empresaId' => (int) $empresaId,
    'rolId' => (int) $rolId,
    'batch' => $batch,
    'accesoTotal' => $hasAcceso,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
