<?php

use App\Http\Controllers\Api\V1\GridLayoutsController;
use App\Http\Controllers\Api\V1\PivotLayoutsController;
use App\Http\Controllers\Api\V1\ParametrosController;
use App\Http\Controllers\Api\V1\Admin\EmpresasController as AdminEmpresasController;
use App\Http\Controllers\Api\V1\Admin\PermisosController as AdminPermisosController;
use App\Http\Controllers\Api\V1\Admin\RolAtributosController as AdminRolAtributosController;
use App\Http\Controllers\Api\V1\Admin\RolesController as AdminRolesController;
use App\Http\Controllers\Api\V1\Admin\UsuariosController as AdminUsuariosController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Partes\PartesInformeController;
use App\Http\Controllers\Api\V1\Partes\PartesMaestrosController;
use App\Http\Controllers\Api\V1\Partes\PartesTareaController;
use App\Http\Controllers\Api\V1\SystemStatusController;
use App\Http\Controllers\Api\V1\User\EmpresasController;
use App\Http\Controllers\Api\V1\User\MenuController;
use App\Http\Controllers\Api\V1\User\PreferencesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::middleware('paqsuite.instalacion')->prefix('auth')->group(function () {
        Route::post('/login', LoginController::class);
        Route::post('/forgot-password', ForgotPasswordController::class)->middleware('throttle:5,1');
        Route::post('/reset-password', ResetPasswordController::class);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', LogoutController::class);
            Route::get('/me', MeController::class)->middleware('partes.profile');
            Route::post('/change-password', ChangePasswordController::class);
        });
    });

    Route::middleware(['paqsuite.instalacion', 'auth:sanctum'])->group(function () {
        Route::get('/user/menu', MenuController::class);
        Route::get('/user/preferences', [PreferencesController::class, 'show']);
        Route::patch('/user/preferences', [PreferencesController::class, 'update']);
        Route::get('/empresas', EmpresasController::class);
        Route::get('/system/status', [SystemStatusController::class, 'show']);
        Route::get('/parametros', [ParametrosController::class, 'index']);
        Route::patch('/parametros/{clave}', [ParametrosController::class, 'update']);

        Route::get('/grid-layouts/active', [GridLayoutsController::class, 'active']);
        Route::put('/grid-layouts/active', [GridLayoutsController::class, 'setActive']);
        Route::get('/grid-layouts', [GridLayoutsController::class, 'index']);
        Route::post('/grid-layouts', [GridLayoutsController::class, 'store']);
        Route::put('/grid-layouts/{id}', [GridLayoutsController::class, 'update']);
        Route::delete('/grid-layouts/{id}', [GridLayoutsController::class, 'destroy']);

        Route::get('/pivot-layouts/active', [PivotLayoutsController::class, 'active']);
        Route::put('/pivot-layouts/active', [PivotLayoutsController::class, 'setActive']);
        Route::get('/pivot-layouts', [PivotLayoutsController::class, 'index']);
        Route::post('/pivot-layouts', [PivotLayoutsController::class, 'store']);
        Route::put('/pivot-layouts/{id}', [PivotLayoutsController::class, 'update']);
        Route::delete('/pivot-layouts/{id}', [PivotLayoutsController::class, 'destroy']);

        // Lectura amplia (lookup maestros Partes vía ?soloActivos= + listado Admin Seguridad).
        Route::get('/admin/usuarios', [AdminUsuariosController::class, 'index']);

        // ABM Seguridad GEN-06 (AccesoTotal empresa activa).
        Route::middleware('paqsuite.seguridadAdmin')->prefix('admin')->group(function () {
            Route::post('/usuarios', [AdminUsuariosController::class, 'store']);
            Route::patch('/usuarios/{id}', [AdminUsuariosController::class, 'update']);
            Route::delete('/usuarios/{id}', [AdminUsuariosController::class, 'destroy']);

            // MONO: empresas sin alta/baja — solo consulta y edición.
            Route::get('/empresas', [AdminEmpresasController::class, 'index']);
            Route::get('/empresas/{id}', [AdminEmpresasController::class, 'show']);
            Route::put('/empresas/{id}', [AdminEmpresasController::class, 'update']);

            Route::get('/roles', [AdminRolesController::class, 'index']);
            Route::post('/roles', [AdminRolesController::class, 'store']);
            Route::patch('/roles/{id}', [AdminRolesController::class, 'update']);
            Route::delete('/roles/{id}', [AdminRolesController::class, 'destroy']);
            Route::get('/roles/{id}/atributos', [AdminRolAtributosController::class, 'show'])->whereNumber('id');
            Route::put('/roles/{id}/atributos', [AdminRolAtributosController::class, 'update'])->whereNumber('id');

            Route::get('/permisos', [AdminPermisosController::class, 'index']);
            Route::post('/permisos', [AdminPermisosController::class, 'store']);
            Route::post('/permisos/batch', [AdminPermisosController::class, 'batch']);
            Route::delete('/permisos/{id}', [AdminPermisosController::class, 'destroy']);
        });

        // Lectura: dashboard / informes (incluye perfil cliente)
        Route::middleware(['partes.profile'])->prefix('partes')->group(function () {
            $i = PartesInformeController::class;
            Route::get('/parametros/dashboard', [$i, 'dashboardParametros']);
            Route::get('/dashboard', [$i, 'dashboard']);
            Route::get('/informes/tareas', [$i, 'listTareas']);
            Route::get('/informes/agrupado', [$i, 'agrupado']);
            Route::get('/informes/paquete-horas', [$i, 'paqueteHoras']);
        });

        // Operación / maestros: no cliente
        Route::middleware(['partes.profile', 'partes.notCliente'])->prefix('partes')->group(function () {
            $c = PartesMaestrosController::class;
            Route::get('/asistentes', [$c, 'listAsistentes']);
            Route::get('/asistentes/{id}', [$c, 'getAsistente'])->whereNumber('id');
            Route::post('/asistentes', [$c, 'storeAsistente']);
            Route::put('/asistentes/{id}', [$c, 'updateAsistente'])->whereNumber('id');
            Route::patch('/asistentes/{id}/estado', [$c, 'patchAsistenteEstado'])->whereNumber('id');
            Route::delete('/asistentes/{id}', [$c, 'deleteAsistente'])->whereNumber('id');

            Route::get('/clientes', [$c, 'listClientes']);
            Route::get('/clientes/{id}', [$c, 'getCliente'])->whereNumber('id');
            Route::post('/clientes', [$c, 'storeCliente']);
            Route::put('/clientes/{id}', [$c, 'updateCliente'])->whereNumber('id');
            Route::patch('/clientes/{id}/estado', [$c, 'patchClienteEstado'])->whereNumber('id');
            Route::delete('/clientes/{id}', [$c, 'deleteCliente'])->whereNumber('id');
            Route::post('/clientes/{id}/acceso', [$c, 'setClienteAcceso'])->whereNumber('id');
            Route::delete('/clientes/{id}/acceso', [$c, 'revokeClienteAcceso'])->whereNumber('id');

            Route::get('/tipos-cliente', [$c, 'listTiposCliente']);
            Route::get('/tipos-cliente/{id}', [$c, 'getTipoCliente'])->whereNumber('id');
            Route::post('/tipos-cliente', [$c, 'storeTipoCliente']);
            Route::put('/tipos-cliente/{id}', [$c, 'updateTipoCliente'])->whereNumber('id');
            Route::patch('/tipos-cliente/{id}/estado', [$c, 'patchTipoClienteEstado'])->whereNumber('id');
            Route::delete('/tipos-cliente/{id}', [$c, 'deleteTipoCliente'])->whereNumber('id');

            Route::get('/tipos-tarea', [$c, 'listTiposTarea']);
            Route::get('/tipos-tarea/{id}', [$c, 'getTipoTarea'])->whereNumber('id');
            Route::post('/tipos-tarea', [$c, 'storeTipoTarea']);
            Route::put('/tipos-tarea/{id}', [$c, 'updateTipoTarea'])->whereNumber('id');
            Route::patch('/tipos-tarea/{id}/estado', [$c, 'patchTipoTareaEstado'])->whereNumber('id');
            Route::delete('/tipos-tarea/{id}', [$c, 'deleteTipoTarea'])->whereNumber('id');

            Route::get('/cliente-tipos-tarea', [$c, 'listAsignaciones']);
            Route::post('/cliente-tipos-tarea', [$c, 'storeAsignacion']);
            Route::delete('/cliente-tipos-tarea/{id}', [$c, 'deleteAsignacion'])->whereNumber('id');

            Route::get('/catalogos/clientes', [$c, 'catalogoClientes']);
            Route::get('/catalogos/asistentes', [$c, 'catalogoAsistentes']);
            Route::get('/catalogos/tipos-cliente', [$c, 'catalogoTiposCliente']);
            Route::get('/catalogos/tipos-tarea', [$c, 'catalogoTiposTarea']);

            $t = PartesTareaController::class;
            Route::get('/parametros/duracion-tramo', [$t, 'duracionTramo']);
            Route::get('/tareas', [$t, 'list']);
            Route::get('/tareas/ids', [$t, 'listIds']);
            Route::post('/tareas/masivo/set-cerrado', [$t, 'masivoSetCerrado']);
            Route::post('/tareas/masivo/actualizar', [$t, 'masivoActualizar']);
            Route::get('/tareas/{id}', [$t, 'show'])->whereNumber('id');
            Route::post('/tareas', [$t, 'store']);
            Route::put('/tareas/{id}', [$t, 'update'])->whereNumber('id');
            Route::delete('/tareas/{id}', [$t, 'destroy'])->whereNumber('id');
            Route::post('/tareas/{id}/cerrar', [$t, 'cerrar'])->whereNumber('id');
            Route::post('/tareas/{id}/reabrir', [$t, 'reabrir'])->whereNumber('id');
        });
    });
});
