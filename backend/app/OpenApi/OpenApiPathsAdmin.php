<?php

namespace App\OpenApi;

/**
 * Admin seguridad GEN-06.
 *
 * @OA\Get(
 *   path="/api/v1/admin/usuarios",
 *   operationId="adminUsuariosIndex",
 *   tags={"Admin"},
 *   summary="Listar usuarios",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/admin/usuarios",
 *   operationId="adminUsuariosStore",
 *   tags={"Admin"},
 *   summary="Alta usuario",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="usuario", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="password", type="string", format="password")
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Patch(
 *   path="/api/v1/admin/usuarios/{id}",
 *   operationId="adminUsuariosUpdate",
 *   tags={"Admin"},
 *   summary="Actualizar usuario",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Delete(
 *   path="/api/v1/admin/usuarios/{id}",
 *   operationId="adminUsuariosDestroy",
 *   tags={"Admin"},
 *   summary="Eliminar usuario",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/admin/empresas",
 *   operationId="adminEmpresasIndex",
 *   tags={"Admin"},
 *   summary="Listar empresas (MONO: consulta)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/admin/empresas/{id}",
 *   operationId="adminEmpresasShow",
 *   tags={"Admin"},
 *   summary="Detalle empresa",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/admin/empresas/{id}",
 *   operationId="adminEmpresasUpdate",
 *   tags={"Admin"},
 *   summary="Actualizar empresa",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/admin/roles",
 *   operationId="adminRolesIndex",
 *   tags={"Admin"},
 *   summary="Listar roles",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/admin/roles",
 *   operationId="adminRolesStore",
 *   tags={"Admin"},
 *   summary="Alta rol",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Patch(
 *   path="/api/v1/admin/roles/{id}",
 *   operationId="adminRolesUpdate",
 *   tags={"Admin"},
 *   summary="Actualizar rol",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Delete(
 *   path="/api/v1/admin/roles/{id}",
 *   operationId="adminRolesDestroy",
 *   tags={"Admin"},
 *   summary="Eliminar rol",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/admin/roles/{id}/atributos",
 *   operationId="adminRolAtributosShow",
 *   tags={"Admin"},
 *   summary="Atributos del rol",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/admin/roles/{id}/atributos",
 *   operationId="adminRolAtributosUpdate",
 *   tags={"Admin"},
 *   summary="Actualizar atributos del rol",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/admin/permisos",
 *   operationId="adminPermisosIndex",
 *   tags={"Admin"},
 *   summary="Listar permisos",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/admin/permisos",
 *   operationId="adminPermisosStore",
 *   tags={"Admin"},
 *   summary="Alta permiso",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/admin/permisos/batch",
 *   operationId="adminPermisosBatch",
 *   tags={"Admin"},
 *   summary="Alta/baja permisos en lote",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Delete(
 *   path="/api/v1/admin/permisos/{id}",
 *   operationId="adminPermisosDestroy",
 *   tags={"Admin"},
 *   summary="Eliminar permiso",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 */
abstract class OpenApiPathsAdmin
{
}
