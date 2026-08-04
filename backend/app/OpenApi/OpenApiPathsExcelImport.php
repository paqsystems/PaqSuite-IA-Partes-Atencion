<?php

namespace App\OpenApi;

/**
 * Importación Excel GEN-14 (TR-009) — processCode partes.tareas.import.
 *
 * @OA\Get(
 *   path="/api/v1/excel-import/processes/{codigo}/template",
 *   operationId="excelImportTemplate",
 *   tags={"Excel Import"},
 *   summary="Descargar plantilla XLSX del proceso",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="codigo", in="path", required=true, @OA\Schema(type="string", example="partes.tareas.import")),
 *   @OA\Response(response=200, description="Archivo XLSX"),
 *   @OA\Response(response=404, description="Proceso no encontrado")
 * )
 *
 * @OA\Post(
 *   path="/api/v1/excel-import/batches",
 *   operationId="excelImportBatchesStore",
 *   tags={"Excel Import"},
 *   summary="Crear lote (upload + validación)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         required={"processCode","file"},
 *         @OA\Property(property="processCode", type="string", example="partes.tareas.import"),
 *         @OA\Property(property="file", type="string", format="binary")
 *       )
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/excel-import/batches/{batchId}",
 *   operationId="excelImportBatchesShow",
 *   tags={"Excel Import"},
 *   summary="Estado del lote",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="batchId", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/excel-import/batches/{batchId}/errors",
 *   operationId="excelImportBatchesErrors",
 *   tags={"Excel Import"},
 *   summary="Errores de validación del lote",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="batchId", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/excel-import/batches/{batchId}/errors/export",
 *   operationId="excelImportBatchesErrorsExport",
 *   tags={"Excel Import"},
 *   summary="Exportar errores a XLSX",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="batchId", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="Archivo XLSX")
 * )
 *
 * @OA\Post(
 *   path="/api/v1/excel-import/batches/{batchId}/process",
 *   operationId="excelImportBatchesProcess",
 *   tags={"Excel Import"},
 *   summary="Procesar filas válidas del lote",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="batchId", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 */
abstract class OpenApiPathsExcelImport
{
}
