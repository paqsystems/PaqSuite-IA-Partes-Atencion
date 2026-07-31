<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\UserEmpresasQueryRepository;

final class SpUserEmpresasQueryRepository implements UserEmpresasQueryRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listForUser(int $userId): array
    {
        $rows = $this->spCaller->call('pq_sp_user_empresas_list', [
            'user_id' => $userId,
        ]);

        return array_map(static function (object $row): array {
            return [
                'id' => (int) ($row->id ?? 0),
                'nombreEmpresa' => (string) ($row->nombreEmpresa ?? ''),
                'theme' => isset($row->theme) ? (string) $row->theme : null,
            ];
        }, $rows);
    }
}
