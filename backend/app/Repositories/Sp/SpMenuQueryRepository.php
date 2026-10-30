<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Menu\MenuQueryRepository;
use PaqSuite\LaravelCore\Menu\StoredProcedureMenuQueryRepository;

final class SpMenuQueryRepository implements MenuQueryRepository
{
    public function __construct(
        private readonly string $dictionaryConnection,
    ) {
    }

    public function listEnabled(): array
    {
        return (new StoredProcedureMenuQueryRepository($this->dictionaryConnection))->listEnabled();
    }
}
