<?php

namespace App\OpenApi;

/**
 * Emisiones GEN-15 (TR-011) — processCode partes.informes.consultaDetallada.
 * Extensión host: hostContext en POST preview/jobs.
 *
 * @OA\Tag(name="Emisiones", description="Emisión de reportes GEN-15 (Consulta detallada)")
 *
 * @OA\Get(
 *   path="/api/v1/emissions/processes/{processCode}",
 *   operationId="emissionsShowProcess",
 *   tags={"Emisiones"},
 *   summary="Metadata del proceso de emisión",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="processCode", in="path", required=true, @OA\Schema(type="string", example="partes.informes.consultaDetallada")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=403, description="Capacidad off (4704) o sin menú (4703)"),
 *   @OA\Response(response=404, description="Proceso no encontrado (4705)")
 * )
 *
 * @OA\Post(
 *   path="/api/v1/emissions/preview",
 *   operationId="emissionsPreview",
 *   tags={"Emisiones"},
 *   summary="Vista previa (no bloquea emitir; requierePreview=false en Consulta detallada)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"processCode","channel","mode","hostContext"},
 *       @OA\Property(property="processCode", type="string", example="partes.informes.consultaDetallada"),
 *       @OA\Property(property="channel", type="string", example="pdf"),
 *       @OA\Property(property="mode", type="string", example="consolidated"),
 *       @OA\Property(property="hostContext", ref="#/components/schemas/ConsultaDetalladaHostContext")
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/emissions/jobs",
 *   operationId="emissionsJobsStore",
 *   tags={"Emisiones"},
 *   summary="Crear job de emisión",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"processCode","channel","mode","hostContext"},
 *       @OA\Property(property="processCode", type="string", example="partes.informes.consultaDetallada"),
 *       @OA\Property(property="channel", type="string", example="pdf"),
 *       @OA\Property(property="mode", type="string", example="consolidated"),
 *       @OA\Property(property="mailTo", type="array", @OA\Items(type="string"), example={"ops@example.com"}),
 *       @OA\Property(property="hostContext", ref="#/components/schemas/ConsultaDetalladaHostContext")
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK síncrono", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=202, description="Encolado")
 * )
 *
 * @OA\Get(
 *   path="/api/v1/emissions/jobs/{jobId}",
 *   operationId="emissionsJobsShow",
 *   tags={"Emisiones"},
 *   summary="Estado del job",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="jobId", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/emissions/jobs/{jobId}/download",
 *   operationId="emissionsJobsDownload",
 *   tags={"Emisiones"},
 *   summary="Descargar artefacto (binario)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="jobId", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="Archivo")
 * )
 *
 * @OA\Get(
 *   path="/api/v1/emissions/design/processes/{processCode}/reports",
 *   operationId="emissionsDesignReports",
 *   tags={"Emisiones"},
 *   summary="Listar reportes para el diseñador (requiere emission.design / AccesoTotal)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="processCode", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=403, description="Sin permiso de diseño (4709)")
 * )
 *
 * @OA\Schema(
 *   schema="ConsultaDetalladaHostContext",
 *   type="object",
 *   required={"fechaDesde","fechaHasta","estadoCerrado"},
 *   @OA\Property(property="fechaDesde", type="string", format="date", example="2026-08-01"),
 *   @OA\Property(property="fechaHasta", type="string", format="date", example="2026-08-31"),
 *   @OA\Property(property="clienteId", type="integer", nullable=true),
 *   @OA\Property(property="usuarioId", type="integer", nullable=true, description="Ignorado si el actor no es supervisor"),
 *   @OA\Property(property="tipoTareaId", type="integer", nullable=true),
 *   @OA\Property(property="estadoCerrado", type="string", enum={"todas","abiertas","cerradas"})
 * )
 */
abstract class OpenApiPathsEmissions
{
}
