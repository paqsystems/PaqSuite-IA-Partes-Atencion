<?php

use App\Http\Controllers\DxReportingProxyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Proxy diseñador DevExpress Reporting (sin CSRF / sesión)
|--------------------------------------------------------------------------
|
| El SPA en Vercel llama /DXXRD* same-origin; Vercel reescribe a este host
| y Laravel reenvía al sidecar ASP.NET en 127.0.0.1:5055.
|
*/

Route::any('DXXRD/{path?}', [DxReportingProxyController::class, 'forward'])->where('path', '.*');
Route::any('DXXRDV/{path?}', [DxReportingProxyController::class, 'forward'])->where('path', '.*');
Route::any('DXXQB/{path?}', [DxReportingProxyController::class, 'forward'])->where('path', '.*');
