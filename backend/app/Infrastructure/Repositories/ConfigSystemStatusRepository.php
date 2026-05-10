<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SystemStatusRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class ConfigSystemStatusRepository implements SystemStatusRepositoryInterface
{
    public function __construct(
        private readonly ConfigRepository $config
    ) {
    }

    public function getStatusSnapshot(): array
    {
        return [
            'installationMode' => $this->config->get('paqsuite.installation_mode', 'MONO'),
            'appName' => $this->config->get('app.name'),
            'environment' => $this->config->get('app.env'),
        ];
    }
}
