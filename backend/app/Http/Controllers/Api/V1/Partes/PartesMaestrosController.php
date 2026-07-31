<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Controller;
use App\Repositories\Sp\Partes\PartesMaestrosRepository;
use App\Services\Partes\PartesMaestrosException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesMaestrosController extends Controller
{
    public function __construct(private readonly PartesMaestrosRepository $repository)
    {
    }

    public function listAsistentes(Request $request): JsonResponse
    {
        return $this->list('pq_sp_partes_usuarios_list', $request);
    }

    public function getAsistente(int $id): JsonResponse
    {
        return $this->get('pq_sp_partes_usuarios_get', $id);
    }

    public function storeAsistente(Request $request): JsonResponse
    {
        return $this->upsert('pq_sp_partes_usuarios_upsert', $request, null, [
            'userId' => 'p_user_id',
            'code' => 'p_code',
            'nombre' => 'p_nombre',
            'email' => 'p_email',
            'supervisor' => 'p_supervisor',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function updateAsistente(Request $request, int $id): JsonResponse
    {
        return $this->upsert('pq_sp_partes_usuarios_upsert', $request, $id, [
            'userId' => 'p_user_id',
            'code' => 'p_code',
            'nombre' => 'p_nombre',
            'email' => 'p_email',
            'supervisor' => 'p_supervisor',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function patchAsistenteEstado(Request $request, int $id): JsonResponse
    {
        return $this->patchEstado('pq_sp_partes_usuarios_set_estado', $request, $id);
    }

    public function deleteAsistente(int $id): JsonResponse
    {
        return $this->delete('pq_sp_partes_usuarios_delete', $id);
    }

    public function listClientes(Request $request): JsonResponse
    {
        return $this->list('pq_sp_partes_clientes_list', $request);
    }

    public function getCliente(int $id): JsonResponse
    {
        return $this->get('pq_sp_partes_clientes_get', $id);
    }

    public function storeCliente(Request $request): JsonResponse
    {
        return $this->upsert('pq_sp_partes_clientes_upsert', $request, null, [
            'userId' => 'p_user_id',
            'code' => 'p_code',
            'nombre' => 'p_nombre',
            'email' => 'p_email',
            'tipoClienteId' => 'p_tipo_cliente_id',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function updateCliente(Request $request, int $id): JsonResponse
    {
        return $this->upsert('pq_sp_partes_clientes_upsert', $request, $id, [
            'userId' => 'p_user_id',
            'code' => 'p_code',
            'nombre' => 'p_nombre',
            'email' => 'p_email',
            'tipoClienteId' => 'p_tipo_cliente_id',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function patchClienteEstado(Request $request, int $id): JsonResponse
    {
        return $this->patchEstado('pq_sp_partes_clientes_set_estado', $request, $id);
    }

    public function deleteCliente(int $id): JsonResponse
    {
        return $this->delete('pq_sp_partes_clientes_delete', $id);
    }

    public function setClienteAcceso(Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_clientes_set_acceso', [
                'p_id' => $id,
                'p_user_id' => $request->input('userId'),
            ]);

            return ApiResponse::success(['item' => $item]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    public function revokeClienteAcceso(int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_clientes_set_acceso', [
                'p_id' => $id,
                'p_user_id' => null,
            ]);

            return ApiResponse::success(['item' => $item]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    public function listTiposCliente(Request $request): JsonResponse
    {
        return $this->list('pq_sp_partes_tipos_cliente_list', $request);
    }

    public function getTipoCliente(int $id): JsonResponse
    {
        return $this->get('pq_sp_partes_tipos_cliente_get', $id);
    }

    public function storeTipoCliente(Request $request): JsonResponse
    {
        return $this->upsert('pq_sp_partes_tipos_cliente_upsert', $request, null, [
            'code' => 'p_code',
            'descripcion' => 'p_descripcion',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function updateTipoCliente(Request $request, int $id): JsonResponse
    {
        return $this->upsert('pq_sp_partes_tipos_cliente_upsert', $request, $id, [
            'code' => 'p_code',
            'descripcion' => 'p_descripcion',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function patchTipoClienteEstado(Request $request, int $id): JsonResponse
    {
        return $this->patchEstado('pq_sp_partes_tipos_cliente_set_estado', $request, $id);
    }

    public function deleteTipoCliente(int $id): JsonResponse
    {
        return $this->delete('pq_sp_partes_tipos_cliente_delete', $id);
    }

    public function listTiposTarea(Request $request): JsonResponse
    {
        return $this->list('pq_sp_partes_tipos_tarea_list', $request);
    }

    public function getTipoTarea(int $id): JsonResponse
    {
        return $this->get('pq_sp_partes_tipos_tarea_get', $id);
    }

    public function storeTipoTarea(Request $request): JsonResponse
    {
        return $this->upsert('pq_sp_partes_tipos_tarea_upsert', $request, null, [
            'code' => 'p_code',
            'descripcion' => 'p_descripcion',
            'isGenerico' => 'p_is_generico',
            'isDefault' => 'p_is_default',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function updateTipoTarea(Request $request, int $id): JsonResponse
    {
        return $this->upsert('pq_sp_partes_tipos_tarea_upsert', $request, $id, [
            'code' => 'p_code',
            'descripcion' => 'p_descripcion',
            'isGenerico' => 'p_is_generico',
            'isDefault' => 'p_is_default',
            'activo' => 'p_activo',
            'inhabilitado' => 'p_inhabilitado',
        ]);
    }

    public function patchTipoTareaEstado(Request $request, int $id): JsonResponse
    {
        return $this->patchEstado('pq_sp_partes_tipos_tarea_set_estado', $request, $id);
    }

    public function deleteTipoTarea(int $id): JsonResponse
    {
        return $this->delete('pq_sp_partes_tipos_tarea_delete', $id);
    }

    public function listAsignaciones(Request $request): JsonResponse
    {
        try {
            $items = $this->repository->call('pq_sp_partes_cliente_tipo_tarea_list', [
                'p_cliente_id' => $request->query('clienteId'),
            ]);

            return ApiResponse::success(['items' => $items, 'total' => count($items)]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    public function storeAsignacion(Request $request): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_cliente_tipo_tarea_upsert', [
                'p_cliente_id' => $request->input('clienteId'),
                'p_tipo_tarea_id' => $request->input('tipoTareaId'),
            ]);

            return ApiResponse::success(['item' => $item], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    public function deleteAsignacion(int $id): JsonResponse
    {
        return $this->delete('pq_sp_partes_cliente_tipo_tarea_delete', $id);
    }

    public function catalogoClientes(): JsonResponse
    {
        return $this->catalogo('pq_sp_partes_catalogo_clientes');
    }

    public function catalogoAsistentes(): JsonResponse
    {
        return $this->catalogo('pq_sp_partes_catalogo_usuarios_dominio');
    }

    public function catalogoTiposCliente(): JsonResponse
    {
        return $this->catalogo('pq_sp_partes_catalogo_tipos_cliente');
    }

    public function catalogoTiposTarea(Request $request): JsonResponse
    {
        try {
            $items = $this->repository->call('pq_sp_partes_catalogo_tipos_tarea_universo', [
                'p_cliente_id' => $request->query('clienteId'),
            ]);

            return ApiResponse::success(['items' => $items]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    private function list(string $procedure, Request $request): JsonResponse
    {
        try {
            $result = $this->repository->list($procedure, [
                'p_code' => $request->query('code'),
                'p_page' => $request->query('page', 1),
                'p_page_size' => $request->query('pageSize', 50),
            ]);

            return ApiResponse::success($result);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    private function get(string $procedure, int $id): JsonResponse
    {
        try {
            return ApiResponse::success(['item' => $this->repository->getOne($procedure, ['p_id' => $id])]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    /**
     * @param  array<string, string>  $map
     */
    private function upsert(string $procedure, Request $request, ?int $id, array $map): JsonResponse
    {
        try {
            $params = ['p_id' => $id];
            foreach ($map as $camel => $spParam) {
                if ($request->exists($camel)) {
                    $params[$spParam] = $request->input($camel);
                }
            }
            $item = $this->repository->getOne($procedure, $params);

            return ApiResponse::success(['item' => $item], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, $id === null ? 201 : 200);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    private function patchEstado(string $procedure, Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne($procedure, [
                'p_id' => $id,
                'p_activo' => $request->input('activo'),
                'p_inhabilitado' => $request->input('inhabilitado'),
            ]);

            return ApiResponse::success(['item' => $item]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    private function delete(string $procedure, int $id): JsonResponse
    {
        try {
            $this->repository->call($procedure, ['p_id' => $id]);

            return ApiResponse::success([]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    private function catalogo(string $procedure): JsonResponse
    {
        try {
            $items = $this->repository->call($procedure);

            return ApiResponse::success(['items' => $items]);
        } catch (PartesMaestrosException $e) {
            return $this->fromException($e);
        }
    }

    private function fromException(PartesMaestrosException $e): JsonResponse
    {
        $error = $e->httpStatus === 404
            ? PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND
            : ($e->httpStatus === 403
                ? PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN
                : PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);

        return ApiResponse::error($error, $e->respuesta, $e->httpStatus);
    }
}
