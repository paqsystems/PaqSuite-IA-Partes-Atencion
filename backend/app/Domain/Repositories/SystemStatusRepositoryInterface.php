<?php

namespace App\Domain\Repositories;

interface SystemStatusRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getStatusSnapshot(): array;
}
