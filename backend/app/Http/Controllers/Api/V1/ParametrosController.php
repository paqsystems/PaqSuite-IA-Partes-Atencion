<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Parametros\ParametroService;
use PaqSuite\LaravelCore\Parametros\ParametrosException;

/**
 * GEN-10 — `/api/v1/parametros` (Auth + Partes).
 * Con rol AccesoTotal (seed SUPERVISOR) no se exige permiso fino por programa.
 */
final class ParametrosController extends Controller
{
    public function __construct(private readonly ParametroService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $programa = trim((string) $request->query('programa', ''));
        if ($programa === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::PARAMETROS_VALIDATION_FAILED);
        }

        try {
            return ApiResponse::success(['items' => $this->service->list($programa)]);
        } catch (ParametrosException $exception) {
            return $this->fromParametrosException($exception);
        }
    }

    public function update(Request $request, string $clave): JsonResponse
    {
        if (! $request->exists('programa') || ! $request->exists('valor')) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::PARAMETROS_VALIDATION_FAILED);
        }

        $programa = trim((string) $request->input('programa', ''));
        if ($programa === '' || trim($clave) === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::PARAMETROS_VALIDATION_FAILED);
        }

        $user = $request->user();
        $actorId = $user !== null ? (int) $user->id : 0;

        try {
            $item = $this->service->patch($clave, $programa, $request->input('valor'), $actorId);

            return ApiResponse::success(['item' => $item]);
        } catch (ParametrosException $exception) {
            return $this->fromParametrosException($exception);
        }
    }

    private function fromParametrosException(ParametrosException $exception): JsonResponse
    {
        return ApiResponse::error(
            PaqSuiteEnvelopeCatalog::PARAMETROS_VALIDATION_FAILED,
            $exception->messageKey,
            $exception->httpStatus
        );
    }
}
