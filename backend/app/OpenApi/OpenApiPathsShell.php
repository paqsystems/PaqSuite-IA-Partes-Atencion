<?php

namespace App\OpenApi;

/**
 * Parámetros generales y layouts de grilla/pivot.
 *
 * @OA\Get(
 *   path="/api/v1/parametros",
 *   operationId="parametrosIndex",
 *   tags={"Parametros"},
 *   summary="Listar parámetros",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="programa", in="query", @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Patch(
 *   path="/api/v1/parametros/{clave}",
 *   operationId="parametrosUpdate",
 *   tags={"Parametros"},
 *   summary="Actualizar valor de parámetro",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="clave", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\RequestBody(@OA\JsonContent(@OA\Property(property="valor", type="string"))),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/grid-layouts/active",
 *   operationId="gridLayoutsActive",
 *   tags={"Grid Layouts"},
 *   summary="Layout de grilla activo",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="gridKey", in="query", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/grid-layouts/active",
 *   operationId="gridLayoutsSetActive",
 *   tags={"Grid Layouts"},
 *   summary="Marcar layout de grilla activo",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="gridKey", type="string"),
 *     @OA\Property(property="layoutId", type="integer", nullable=true)
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/grid-layouts",
 *   operationId="gridLayoutsIndex",
 *   tags={"Grid Layouts"},
 *   summary="Listar layouts de grilla",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="gridKey", in="query", @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/grid-layouts",
 *   operationId="gridLayoutsStore",
 *   tags={"Grid Layouts"},
 *   summary="Crear layout de grilla",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="gridKey", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="state", type="object")
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/grid-layouts/{id}",
 *   operationId="gridLayoutsUpdate",
 *   tags={"Grid Layouts"},
 *   summary="Actualizar layout de grilla",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="state", type="object")
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Delete(
 *   path="/api/v1/grid-layouts/{id}",
 *   operationId="gridLayoutsDestroy",
 *   tags={"Grid Layouts"},
 *   summary="Eliminar layout de grilla",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/pivot-layouts/active",
 *   operationId="pivotLayoutsActive",
 *   tags={"Pivot Layouts"},
 *   summary="Layout de pivot activo",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="pivotKey", in="query", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/pivot-layouts/active",
 *   operationId="pivotLayoutsSetActive",
 *   tags={"Pivot Layouts"},
 *   summary="Marcar layout de pivot activo",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="pivotKey", type="string"),
 *     @OA\Property(property="layoutId", type="integer", nullable=true)
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/pivot-layouts",
 *   operationId="pivotLayoutsIndex",
 *   tags={"Pivot Layouts"},
 *   summary="Listar layouts de pivot",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="pivotKey", in="query", @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/pivot-layouts",
 *   operationId="pivotLayoutsStore",
 *   tags={"Pivot Layouts"},
 *   summary="Crear layout de pivot",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="pivotKey", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="state", type="object")
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/pivot-layouts/{id}",
 *   operationId="pivotLayoutsUpdate",
 *   tags={"Pivot Layouts"},
 *   summary="Actualizar layout de pivot",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="state", type="object")
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Delete(
 *   path="/api/v1/pivot-layouts/{id}",
 *   operationId="pivotLayoutsDestroy",
 *   tags={"Pivot Layouts"},
 *   summary="Eliminar layout de pivot",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 */
abstract class OpenApiPathsShell
{
}
