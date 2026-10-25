<?php

namespace App\Tasks\Backup;

use Illuminate\Contracts\Cache\Repository;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupLock;

final class RdsBackupCacheLock implements RdsBackupLock
{
    public function __construct(
        private readonly Repository $cache,
    ) {
    }

    public function acquire(string $key, int $leaseSeconds): bool
    {
        return $this->cache->add($key, true, max(1, $leaseSeconds));
    }

    public function renew(string $key, int $leaseSeconds): bool
    {
        if (! $this->cache->has($key)) {
            return false;
        }

        $this->cache->put($key, true, max(1, $leaseSeconds));

        return true;
    }

    public function release(string $key): void
    {
        $this->cache->forget($key);
    }
}
