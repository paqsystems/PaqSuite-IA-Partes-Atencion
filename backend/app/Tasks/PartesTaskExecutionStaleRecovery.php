<?php

namespace App\Tasks;

use PaqSuite\LaravelCore\Tasks\TaskExecutionStaleRecovery;

/** Puente host Partes → {@see StaleTaskExecutionRecovery}. */
final class PartesTaskExecutionStaleRecovery implements TaskExecutionStaleRecovery
{
    public function __construct(
        private readonly StaleTaskExecutionRecovery $recovery,
    ) {
    }

    public function recoverOverlaps(): int
    {
        return $this->recovery->recoverOverlaps();
    }

    public function recoverFull(): int
    {
        return $this->recovery->recover();
    }
}
