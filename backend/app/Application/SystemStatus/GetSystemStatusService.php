<?php

namespace App\Application\SystemStatus;

use App\Domain\Repositories\SystemStatusRepositoryInterface;

class GetSystemStatusService
{
    public function __construct(
        private readonly SystemStatusRepositoryInterface $systemStatusRepository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return $this->systemStatusRepository->getStatusSnapshot();
    }
}
