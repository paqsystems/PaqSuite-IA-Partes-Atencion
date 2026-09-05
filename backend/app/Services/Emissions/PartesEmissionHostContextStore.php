<?php

namespace App\Services\Emissions;

use Illuminate\Support\Facades\Cache;

final class PartesEmissionHostContextStore
{
    private const PREFIX = 'paq.emission.hostContext.';

    private const TTL_SECONDS = 86400;

    /**
     * @param  array<string, mixed>  $hostContext
     */
    public function put(string $jobId, array $hostContext): void
    {
        Cache::put(self::PREFIX.$jobId, $hostContext, self::TTL_SECONDS);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $jobId): ?array
    {
        $value = Cache::get(self::PREFIX.$jobId);

        return is_array($value) ? $value : null;
    }
}
