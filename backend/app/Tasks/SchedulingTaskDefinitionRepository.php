<?php

namespace App\Tasks;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\TaskScheduleCalculator;

/**
 * Completa nextRunAt al crear/editar definiciones (el SP due exige next_run_at).
 */
final class SchedulingTaskDefinitionRepository implements TaskDefinitionRepository
{
    /**
     * @param  callable(array<string, mixed>, DateTimeInterface|null): DateTimeImmutable|null  $nextRunCalculator
     */
    public function __construct(
        private readonly TaskDefinitionRepository $inner,
        private readonly mixed $nextRunCalculator = null,
    ) {
    }

    public function listDefinitions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->inner->listDefinitions($filters, $page, $pageSize);
    }

    public function findDefinitionById(int $definitionId): ?array
    {
        return $this->inner->findDefinitionById($definitionId);
    }

    public function createDefinition(array $payload): int
    {
        return $this->inner->createDefinition($this->withNextRunAt($payload));
    }

    public function updateDefinition(int $definitionId, array $payload): bool
    {
        $existing = $this->inner->findDefinitionById($definitionId) ?? [];

        return $this->inner->updateDefinition($definitionId, $this->withNextRunAt($payload, $existing));
    }

    public function softDeactivate(int $definitionId): bool
    {
        return $this->inner->softDeactivate($definitionId);
    }

    public function listCompanyIds(int $definitionId): array
    {
        return $this->inner->listCompanyIds($definitionId);
    }

    public function listDefinitionParameterValues(int $definitionId): array
    {
        return $this->inner->listDefinitionParameterValues($definitionId);
    }

    public function listDueDefinitions(\DateTimeInterface $asOf): array
    {
        return $this->inner->listDueDefinitions($asOf);
    }

    public function updateNextRunAt(int $definitionId, \DateTimeInterface $nextRunAt): bool
    {
        return $this->inner->updateNextRunAt($definitionId, $nextRunAt);
    }

    public function updateLastRun(int $definitionId, \DateTimeInterface $lastRunAt, string $lastStatusCode): bool
    {
        return $this->inner->updateLastRun($definitionId, $lastRunAt, $lastStatusCode);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    private function withNextRunAt(array $payload, array $existing = []): array
    {
        if ($this->hasNextRunAt($payload)) {
            return $payload;
        }

        $schedule = array_merge($existing, $payload);
        $next = $this->calculateNextRunAt($schedule);
        if ($next instanceof DateTimeImmutable) {
            $payload['nextRunAt'] = $next;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasNextRunAt(array $payload): bool
    {
        $value = $payload['nextRunAt'] ?? $payload['next_run_at'] ?? null;
        if ($value instanceof DateTimeInterface) {
            return true;
        }

        return is_string($value) && trim($value) !== '';
    }

    /**
     * @param  array<string, mixed>  $schedule
     */
    private function calculateNextRunAt(array $schedule, ?DateTimeInterface $after = null): ?DateTimeImmutable
    {
        $normalized = [
            'frequencyType' => (string) ($schedule['frequencyType'] ?? $schedule['frequency_type'] ?? ''),
            'frequencyInterval' => $this->asNullableInt($schedule['frequencyInterval'] ?? $schedule['frequency_interval'] ?? null),
            'frequencyDaysMask' => $schedule['frequencyDaysMask'] ?? $schedule['frequency_days_mask'] ?? null,
            'baseDate' => $this->asDate($schedule['baseDate'] ?? $schedule['base_date'] ?? null),
            'baseTime' => $this->asTime($schedule['baseTime'] ?? $schedule['base_time'] ?? null),
        ];

        if ($normalized['baseDate'] === null || $normalized['baseTime'] === null || $normalized['frequencyType'] === '') {
            return null;
        }

        if (is_callable($this->nextRunCalculator)) {
            $computed = ($this->nextRunCalculator)($normalized, $after);

            return $computed instanceof DateTimeImmutable ? $computed : null;
        }

        if (class_exists(TaskScheduleCalculator::class)) {
            try {
                return (new TaskScheduleCalculator())->calculateNextRunAt($normalized, $after);
            } catch (\Throwable) {
                // fallback local
            }
        }

        return $this->fallbackNextRunAt($normalized, $after);
    }

    /**
     * @param  array{
     *     frequencyType: string,
     *     frequencyInterval: int|null,
     *     frequencyDaysMask: mixed,
     *     baseDate: string,
     *     baseTime: string
     * }  $schedule
     */
    private function fallbackNextRunAt(array $schedule, ?DateTimeInterface $after = null): ?DateTimeImmutable
    {
        $tz = new DateTimeZone('UTC');
        $anchor = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $schedule['baseDate'].' '.$schedule['baseTime'], $tz);
        if ($anchor === false) {
            return null;
        }

        $afterAt = $after instanceof DateTimeImmutable
            ? $after->setTimezone($tz)
            : ($after !== null
                ? DateTimeImmutable::createFromInterface($after)->setTimezone($tz)
                : new DateTimeImmutable('now', $tz));

        $type = strtoupper($schedule['frequencyType']);
        $interval = max(1, (int) ($schedule['frequencyInterval'] ?? 1));
        $candidate = $anchor;

        if ($type === 'HOURLY_INTERVAL' || $type === 'HOURLY') {
            while ($candidate <= $afterAt) {
                $candidate = $candidate->add(new DateInterval('PT'.$interval.'H'));
            }

            return $candidate;
        }

        if ($type === 'DAILY') {
            while ($candidate <= $afterAt) {
                $candidate = $candidate->add(new DateInterval('P'.$interval.'D'));
            }

            return $candidate;
        }

        return $candidate > $afterAt ? $candidate : null;
    }

    private function asNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function asDate(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return substr($value, 0, 10);
    }

    private function asTime(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('H:i:s');
        }
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^(\d{2}:\d{2}:\d{2})/', $value, $matches) === 1) {
            return $matches[1];
        }
        if (preg_match('/^(\d{2}:\d{2})$/', $value, $matches) === 1) {
            return $matches[1].':00';
        }

        return null;
    }
}
