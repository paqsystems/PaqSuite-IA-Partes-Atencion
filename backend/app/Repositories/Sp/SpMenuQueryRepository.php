<?php

namespace App\Repositories\Sp;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Menu\MenuQueryRepository;
use PaqSuite\LaravelCore\Menu\StoredProcedureMenuQueryRepository;

final class SpMenuQueryRepository implements MenuQueryRepository
{
    public function __construct(
        private readonly string $dictionaryConnection,
        private readonly SpCaller $spCaller,
    ) {
    }

    public function listEnabled(): array
    {
        $connection = DB::connection($this->dictionaryConnection);
        if ($connection->getDriverName() === 'sqlite') {
            return $this->mapEnabledMenuRows(
                $this->spCaller->call('pq_sp_admin_menu_list_enabled'),
            );
        }

        return (new StoredProcedureMenuQueryRepository($this->dictionaryConnection))->listEnabled();
    }

    /**
     * @param  list<object>  $rows
     * @return list<array<string, mixed>>
     */
    private function mapEnabledMenuRows(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $mapped = (array) $row;
            $parent = $mapped['parentId'] ?? null;
            $labelKey = $mapped['labelKey'] ?? null;
            $routeName = $mapped['routeName'] ?? null;
            $procedimiento = $mapped['procedimiento'] ?? null;
            $processType = $mapped['processType'] ?? null;
            $iconName = $mapped['iconName'] ?? null;

            $items[] = [
                'id' => (int) ($mapped['id'] ?? 0),
                'parentId' => $parent === null || $parent === '' ? null : (int) $parent,
                'menuKey' => (string) ($mapped['menuKey'] ?? ''),
                'labelKey' => $labelKey === null || $labelKey === '' ? null : (string) $labelKey,
                'text' => (string) ($mapped['text'] ?? ''),
                'routeName' => $routeName === null || trim((string) $routeName) === '' ? null : (string) $routeName,
                'order' => (int) ($mapped['order'] ?? 0),
                'procedimiento' => $procedimiento === null || $procedimiento === '' ? null : (string) $procedimiento,
                'processType' => $processType === null || trim((string) $processType) === '' ? null : (string) $processType,
                'iconName' => $iconName === null || $iconName === '' ? null : (string) $iconName,
            ];
        }

        return $items;
    }
}
