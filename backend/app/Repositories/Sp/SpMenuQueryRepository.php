<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Menu\MenuQueryRepository;

final class SpMenuQueryRepository implements MenuQueryRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listEnabled(): array
    {
        $rows = $this->spCaller->call('pq_sp_user_menu', [
            'UserId' => 0,
            'EmpresaId' => 0,
        ]);

        return array_map(static function (object $row): array {
            return [
                'id' => (int) ($row->id ?? 0),
                'parentId' => isset($row->parentId) ? (int) $row->parentId : null,
                'menuKey' => (string) ($row->menuKey ?? $row->codigo ?? ''),
                'codigo' => (string) ($row->menuKey ?? $row->codigo ?? ''),
                'text' => (string) ($row->text ?? $row->titulo ?? ''),
                'titulo' => (string) ($row->text ?? $row->titulo ?? ''),
                'routeName' => isset($row->routeName) ? (string) $row->routeName : null,
                'order' => (int) ($row->order ?? $row->orden ?? 0),
                'orden' => (int) ($row->order ?? $row->orden ?? 0),
                'procedimiento' => isset($row->procedimiento) ? (string) $row->procedimiento : null,
                'processType' => isset($row->processType) ? (string) $row->processType : null,
                'iconName' => isset($row->iconName) ? (string) $row->iconName : null,
            ];
        }, $rows);
    }
}
