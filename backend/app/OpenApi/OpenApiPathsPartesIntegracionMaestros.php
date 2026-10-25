<?php

namespace App\OpenApi;

/**
 * APIs REST de reconciliación completa de maestros Partes (TR-014).
 *
 * @OA\Schema(
 *   schema="PartesIntegracionMaestrosResultado",
 *   type="object",
 *   required={"recurso","dryRun","recibidos","aplicados","omitidos","rechazados","resultados","errores"},
 *   @OA\Property(property="recurso", type="string", example="clientes"),
 *   @OA\Property(property="dryRun", type="boolean", example=false),
 *   @OA\Property(property="recibidos", type="integer", example=3),
 *   @OA\Property(property="aplicados", type="integer", example=2),
 *   @OA\Property(property="omitidos", type="integer", example=0),
 *   @OA\Property(property="rechazados", type="integer", example=1),
 *   @OA\Property(property="erroresTruncados", type="boolean", example=false),
 *   @OA\Property(property="resultados", type="array", @OA\Items(type="object", @OA\Property(property="indice", type="integer", nullable=true, example=0), @OA\Property(property="clave", type="string", nullable=true, example="CLI-001"), @OA\Property(property="accion", type="string", example="modificacion"), @OA\Property(property="codigo", type="string", nullable=true, example="integracion.maestro.itemInvalido"))),
 *   @OA\Property(property="errores", type="array", maxItems=50, @OA\Items(type="object", required={"indice","clave","codigo"}, @OA\Property(property="indice", type="integer", nullable=true, example=2), @OA\Property(property="clave", type="string", example="CLI-404"), @OA\Property(property="codigo", type="string", example="integracion.maestro.referenciaInvalida")))
 * )
 *
 * @OA\Schema(
 *   schema="PartesIntegracionCliente",
 *   type="object",
 *   required={"code","nombre","tipoCliente"},
 *   @OA\Property(property="code", type="string", example="CLI-001"),
 *   @OA\Property(property="nombre", type="string", example="Acme SA"),
 *   @OA\Property(property="tipoCliente", type="object", required={"code"}, @OA\Property(property="code", type="string", example="EMPRESA")),
 *   @OA\Property(property="email", type="string", nullable=true, example="contacto@acme.example"),
 *   @OA\Property(property="erpCliente", type="string", nullable=true, maxLength=15, example="1001"),
 *   @OA\Property(property="erpArticulo", type="string", nullable=true, maxLength=15, example="A-100")
 * )
 *
 * @OA\Schema(
 *   schema="PartesIntegracionAsistente",
 *   type="object",
 *   required={"code","nombre","usuario"},
 *   @OA\Property(property="code", type="string", example="AST-001"),
 *   @OA\Property(property="nombre", type="string", example="Ana Pérez"),
 *   @OA\Property(property="usuario", type="object", required={"code"}, @OA\Property(property="code", type="string", example="USR-001")),
 *   @OA\Property(property="email", type="string", nullable=true, example="ana.perez@example"),
 *   @OA\Property(property="supervisor", type="boolean", default=false, example=false)
 * )
 *
 * @OA\Schema(
 *   schema="PartesIntegracionTipoCliente",
 *   type="object",
 *   required={"code","descripcion"},
 *   @OA\Property(property="code", type="string", example="TC-001"),
 *   @OA\Property(property="descripcion", type="string", example="Empresa")
 * )
 *
 * @OA\Schema(
 *   schema="PartesIntegracionTipoTarea",
 *   type="object",
 *   required={"code","descripcion"},
 *   @OA\Property(property="code", type="string", example="TAR-ADMIN"),
 *   @OA\Property(property="descripcion", type="string", example="Administración"),
 *   @OA\Property(property="isGenerico", type="boolean", default=true, example=true),
 *   @OA\Property(property="isDefault", type="boolean", default=false, example=false)
 * )
 *
 * @OA\Schema(
 *   schema="PartesIntegracionAsignacion",
 *   type="object",
 *   required={"code","tiposTarea"},
 *   @OA\Property(property="code", type="string", example="CLI-001"),
 *   @OA\Property(property="tiposTarea", type="array", @OA\Items(type="object", required={"code"}, @OA\Property(property="code", type="string", example="TAR-CAPAC")))
 * )
 *
 * @OA\Schema(
 *   schema="PartesIntegracionMaestrosRequest",
 *   type="object",
 *   required={"fechaProceso","items"},
 *   @OA\Property(property="fechaProceso", type="string", format="date", example="2026-10-23"),
 *   @OA\Property(property="origen", type="string", nullable=true, maxLength=100, example="ERP"),
 *   @OA\Property(property="dryRun", type="boolean", default=false, example=false),
 *   @OA\Property(property="items", type="array", minItems=1, maxItems=1500, @OA\Items(type="object"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/integracion/maestros/clientes",
 *   operationId="partesIntegracionMaestrosClientes",
 *   tags={"Partes Integración Maestros"},
 *   summary="Reconciliar clientes",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PartesIntegracionMaestrosRequest", example={"fechaProceso"="2026-10-23","origen"="ERP","dryRun"=false,"items"={{"code"="CLI-001","nombre"="Acme SA","tipoCliente"={"code"="EMPRESA"},"email"="contacto@acme.example","erpCliente"="1001","erpArticulo"="A-100"}}})),
 *   @OA\Response(response=200, description="Procesado total o parcialmente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope", example={"error"=0,"respuesta"="integracion.maestro.procesado","resultado"={"recurso"="clientes","dryRun"=false,"recibidos"=3,"aplicados"=2,"omitidos"=0,"rechazados"=1,"erroresTruncados"=false,"resultados"={{"indice"=0,"clave"="CLI-001","accion"="modificacion"},{"indice"=1,"clave"="CLI-002","accion"="inhabilitado"},{"indice"=2,"clave"="CLI-404","accion"="rechazado","codigo"="integracion.maestro.referenciaInvalida"}},"errores"={{"indice"=2,"clave"="CLI-404","codigo"="integracion.maestro.referenciaInvalida"}}}})),
 *   @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=403, description="Permiso insuficiente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=422, description="Contrato, límite o JSON inválido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(path="/api/v1/integracion/maestros/asistentes", operationId="partesIntegracionMaestrosAsistentes", tags={"Partes Integración Maestros"}, summary="Reconciliar asistentes", security={{"sanctum":{}},{"tenant":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PartesIntegracionMaestrosRequest", example={"fechaProceso"="2026-10-23","dryRun"=true,"items"={{"code"="AST-001","nombre"="Ana Pérez","usuario"={"code"="USR-001"},"email"="ana.perez@example","supervisor"=true}}})), @OA\Response(response=200, description="Procesado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=403, description="Permiso insuficiente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=422, description="Contrato inválido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")))
 * @OA\Post(path="/api/v1/integracion/maestros/tipos-cliente", operationId="partesIntegracionMaestrosTiposCliente", tags={"Partes Integración Maestros"}, summary="Reconciliar tipos de cliente", security={{"sanctum":{}},{"tenant":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PartesIntegracionMaestrosRequest", example={"fechaProceso"="2026-10-23","items"={{"code"="TC-001","descripcion"="Empresa"}}})), @OA\Response(response=200, description="Procesado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=403, description="Permiso insuficiente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=422, description="Contrato inválido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")))
 * @OA\Post(path="/api/v1/integracion/maestros/tipos-tarea", operationId="partesIntegracionMaestrosTiposTarea", tags={"Partes Integración Maestros"}, summary="Reconciliar tipos de tarea", security={{"sanctum":{}},{"tenant":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PartesIntegracionMaestrosRequest", example={"fechaProceso"="2026-10-23","dryRun"=false,"items"={{"code"="TAR-ADMIN","descripcion"="Administración","isGenerico"=true,"isDefault"=false}}})), @OA\Response(response=200, description="Procesado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=403, description="Permiso insuficiente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=422, description="Contrato inválido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")))
 * @OA\Post(path="/api/v1/integracion/maestros/asignaciones-cliente-tipo", operationId="partesIntegracionMaestrosAsignaciones", tags={"Partes Integración Maestros"}, summary="Reconciliar asignaciones cliente/tipo", security={{"sanctum":{}},{"tenant":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PartesIntegracionMaestrosRequest", example={"fechaProceso"="2026-10-23","items"={{"code"="CLI-001","tiposTarea"={{"code"="TAR-ADMIN"},{"code"="TAR-CAPAC"}}},{"code"="CLI-002","tiposTarea"={{"code"="TAR-CAPAC"}}}}})), @OA\Response(response=200, description="Procesado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=403, description="Permiso insuficiente", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")), @OA\Response(response=422, description="Contrato inválido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")))
 */
abstract class OpenApiPathsPartesIntegracionMaestros
{
}
