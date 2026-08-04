<?php

namespace App\Services\Partes\SmartCapture;

use Illuminate\Support\Facades\DB;

/**
 * Lookup de maestros usables para Smart Capture (0 / 1 / N).
 */
final class PartesTareaSmartCaptureCatalogResolver
{
    private const MAX_OPTIONS = 10;

    /**
     * @return array{status: 'none'|'one'|'many', item?: array{id: int, code: string, label: string}, options?: list<array{id: int, label: string}>}
     */
    public function resolveCliente(string $query): array
    {
        $term = trim($query);
        if ($term === '') {
            return ['status' => 'none'];
        }

        $rows = DB::table('PQ_PARTES_CLIENTES')
            ->where('activo', 1)
            ->where('inhabilitado', 0)
            ->where(function ($q) use ($term) {
                $q->where('code', 'like', '%'.$term.'%')
                    ->orWhere('nombre', 'like', '%'.$term.'%');
            })
            ->orderBy('code')
            ->limit(self::MAX_OPTIONS + 1)
            ->get(['id', 'code', 'nombre']);

        return $this->classify($rows, static fn ($row) => [
            'id' => (int) $row->id,
            'code' => (string) $row->code,
            'label' => (string) $row->code.' — '.(string) $row->nombre,
        ]);
    }

    /**
     * @return array{status: 'none'|'one'|'many', item?: array{id: int, code: string, label: string}, options?: list<array{id: int, label: string}>}
     */
    public function resolveAsistente(string $query): array
    {
        $term = trim($query);
        if ($term === '') {
            return ['status' => 'none'];
        }

        $rows = DB::table('PQ_PARTES_USUARIOS')
            ->where('activo', 1)
            ->where('inhabilitado', 0)
            ->where(function ($q) use ($term) {
                $q->where('code', 'like', '%'.$term.'%')
                    ->orWhere('nombre', 'like', '%'.$term.'%');
            })
            ->orderBy('code')
            ->limit(self::MAX_OPTIONS + 1)
            ->get(['id', 'code', 'nombre']);

        return $this->classify($rows, static fn ($row) => [
            'id' => (int) $row->id,
            'code' => (string) $row->code,
            'label' => (string) $row->code.' — '.(string) $row->nombre,
        ]);
    }

    /**
     * @return array{status: 'none'|'one'|'many', item?: array{id: int, code: string, label: string}, options?: list<array{id: int, label: string}>}
     */
    public function resolveTipoTarea(string $query, ?int $clienteId): array
    {
        $term = trim($query);
        if ($term === '') {
            return ['status' => 'none'];
        }

        $q = DB::table('PQ_PARTES_TIPOS_TAREA as t')
            ->where('t.activo', 1)
            ->where('t.inhabilitado', 0)
            ->where(function ($inner) use ($term) {
                $inner->where('t.code', 'like', '%'.$term.'%')
                    ->orWhere('t.descripcion', 'like', '%'.$term.'%');
            });

        if ($clienteId !== null && $clienteId > 0) {
            $q->where(function ($inner) use ($clienteId) {
                $inner->where('t.is_generico', 1)
                    ->orWhereExists(function ($sub) use ($clienteId) {
                        $sub->select(DB::raw(1))
                            ->from('PQ_PARTES_CLIENTE_TIPO_TAREA as a')
                            ->whereColumn('a.tipo_tarea_id', 't.id')
                            ->where('a.cliente_id', $clienteId);
                    });
            });
        }

        $rows = $q->orderBy('t.code')
            ->limit(self::MAX_OPTIONS + 1)
            ->get(['t.id', 't.code', 't.descripcion']);

        return $this->classify($rows, static fn ($row) => [
            'id' => (int) $row->id,
            'code' => (string) $row->code,
            'label' => (string) $row->code.' — '.(string) $row->descripcion,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @param  callable(object): array{id: int, code: string, label: string}  $mapper
     * @return array{status: 'none'|'one'|'many', item?: array{id: int, code: string, label: string}, options?: list<array{id: int, label: string}>}
     */
    private function classify($rows, callable $mapper): array
    {
        $mapped = $rows->map($mapper)->values();
        $count = $mapped->count();
        if ($count === 0) {
            return ['status' => 'none'];
        }
        if ($count === 1) {
            return ['status' => 'one', 'item' => $mapped[0]];
        }

        $options = $mapped
            ->take(self::MAX_OPTIONS)
            ->map(static fn (array $item) => [
                'id' => $item['id'],
                'label' => $item['label'],
            ])
            ->all();

        return ['status' => 'many', 'options' => $options];
    }
}
