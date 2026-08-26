<?php

namespace App\Repositories\Sp\Emissions;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionRepository;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionJob;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionProcess;
use PaqSuite\LaravelCore\Parametros\Contracts\ParametroRepository;

/**
 * Adapter host MONO (TR-011): Query Builder sobre pq_emission_*.
 * Contrato alineado a pq_sp_emission_* (script en database/sp para SQL Server).
 */
final class SpEmissionRepository implements EmissionRepository
{
    public function __construct(private readonly ParametroRepository $parametros)
    {
    }

    public function findProcess(string $processCode): ?EmissionProcess
    {
        $row = DB::table('pq_emission_processes')->where('process_code', $processCode)->first();
        if ($row === null) {
            return null;
        }

        return $this->toProcess($row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listProcesses(): array
    {
        return DB::table('pq_emission_processes')
            ->where('is_active', 1)
            ->orderBy('process_code')
            ->get()
            ->map(fn ($row): array => $this->processPayload($this->toProcess($row)))
            ->all();
    }

    public function parameter(string $program, string $key): string|int|null
    {
        $row = $this->parametros->get($program, $key);
        $valor = $row['valor'] ?? null;

        if ($program === 'Emission' && $key === 'EmissionEnabled') {
            if ($valor === true || $valor === 1 || $valor === '1' || strtoupper((string) $valor) === 'S') {
                return 'S';
            }

            return 'N';
        }

        if (is_int($valor)) {
            return $valor;
        }
        if (is_string($valor) || is_numeric($valor)) {
            return is_numeric($valor) && ! is_string($valor) ? (int) $valor : (string) $valor;
        }

        return null;
    }

    public function createJob(EmissionJob $job): void
    {
        DB::table('pq_emission_jobs')->insert([
            'id' => $job->jobId,
            'process_code' => $job->processCode,
            'company_id' => $job->companyId === null ? null : (int) $job->companyId,
            'group_id' => $job->groupId === null ? null : (string) $job->groupId,
            'created_by_user_id' => (int) $job->createdByUserId,
            'status' => $job->status,
            'mode' => $job->mode,
            'channel' => $job->channel,
            'report_id' => $job->reportId === null ? null : (int) $job->reportId,
            'mail_template_id' => $job->mailTemplateId === null ? null : (int) $job->mailTemplateId,
            'preview_session_id' => $job->previewSessionId,
            'dataset_row_count' => $job->datasetRowCount,
            'estimated_size_bytes' => $job->estimatedSizeBytes,
            'artifact_file_name' => $job->fileName,
            'result_message_key' => $job->messageKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findJob(string $jobId, int|string|null $companyId): ?EmissionJob
    {
        $query = DB::table('pq_emission_jobs')->where('id', $jobId);
        if ($companyId !== null) {
            $query->where('company_id', (int) $companyId);
        }
        $row = $query->first();
        if ($row === null) {
            return null;
        }

        $hasArtifact = DB::table('pq_emission_artifacts')->where('job_id', $jobId)->exists();

        return new EmissionJob(
            (string) $row->id,
            (string) $row->process_code,
            $row->company_id === null ? null : (int) $row->company_id,
            (int) $row->created_by_user_id,
            (string) $row->status,
            (string) $row->mode,
            (string) $row->channel,
            $row->company_id === null ? [] : [(int) $row->company_id],
            $row->group_id,
            $row->report_id,
            $row->mail_template_id,
            $row->preview_session_id,
            (int) $row->dataset_row_count,
            (int) $row->estimated_size_bytes,
            $row->artifact_file_name,
            $row->result_message_key,
            $hasArtifact ? (string) $row->id : null,
        );
    }

    public function transition(string $jobId, string $expectedStatus, string $status, array $fields = []): bool
    {
        $patch = [
            'status' => $status,
            'updated_at' => now(),
        ];
        if (array_key_exists('fileName', $fields)) {
            $patch['artifact_file_name'] = $fields['fileName'];
        }
        if (array_key_exists('mimeType', $fields)) {
            $patch['artifact_mime'] = $fields['mimeType'];
        }
        if (array_key_exists('messageKey', $fields)) {
            $patch['result_message_key'] = $fields['messageKey'];
        }
        if (in_array($status, ['done', 'failed'], true)) {
            $patch['finished_at'] = now();
        }

        $updated = DB::table('pq_emission_jobs')
            ->where('id', $jobId)
            ->where('status', $expectedStatus)
            ->update($patch);

        return $updated > 0;
    }

    public function artifactsForPurge(int $olderThanDays): array
    {
        $cutoff = now()->subDays(max(1, $olderThanDays));

        return DB::table('pq_emission_jobs')
            ->whereIn('status', ['done', 'failed'])
            ->whereNotNull('id')
            ->where(function ($query) use ($cutoff) {
                $query->where('finished_at', '<', $cutoff)
                    ->orWhere(function ($inner) use ($cutoff) {
                        $inner->whereNull('finished_at')->where('created_at', '<', $cutoff);
                    });
            })
            ->get(['id'])
            ->map(static fn ($row): array => [
                'jobId' => (string) $row->id,
                'path' => (string) $row->id,
            ])
            ->all();
    }

    public function clearArtifact(string $jobId, string $expectedPath): bool
    {
        if ($expectedPath !== $jobId) {
            return false;
        }
        $deleted = DB::table('pq_emission_artifacts')->where('job_id', $jobId)->delete();

        return $deleted > 0;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function jobRow(string $jobId, int|string|null $companyId): ?array
    {
        $job = $this->findJob($jobId, $companyId);
        if ($job === null) {
            return null;
        }

        return [
            'jobId' => $job->jobId,
            'processCode' => $job->processCode,
            'status' => $job->status,
            'mode' => $job->mode,
            'channel' => $job->channel,
            'fileName' => $job->fileName,
            'messageKey' => $job->messageKey,
            'datasetRowCount' => $job->datasetRowCount,
        ];
    }

    private function toProcess(object $row): EmissionProcess
    {
        $channels = [];
        foreach (['pdf', 'print', 'excel', 'csv', 'mail', 'zip'] as $channel) {
            $column = 'canal_'.$channel;
            if ((bool) ($row->{$column} ?? false)) {
                $channels[] = $channel;
            }
        }

        $reports = DB::table('pq_emission_reports')
            ->where('process_code', $row->process_code)
            ->where('is_active', 1)
            ->orderByDesc('is_principal')
            ->orderBy('id')
            ->get()
            ->map(static fn ($report): array => [
                'id' => (int) $report->id,
                'code' => (string) $report->report_code,
                'name' => (string) $report->name,
                'isPrincipal' => (bool) $report->is_principal,
            ])
            ->all();

        $templates = DB::table('pq_emission_mail_templates')
            ->where('process_code', $row->process_code)
            ->where('is_active', 1)
            ->orderByDesc('is_principal')
            ->orderBy('id')
            ->get()
            ->map(static fn ($template): array => [
                'id' => (int) $template->id,
                'code' => (string) $template->template_code,
                'name' => (string) $template->name,
                'isPrincipal' => (bool) $template->is_principal,
            ])
            ->all();

        return new EmissionProcess(
            (string) $row->process_code,
            (string) $row->menu_process_code,
            $channels,
            (bool) $row->permite_consolidado,
            (bool) $row->permite_segmentado,
            (bool) $row->requiere_vista_previa,
            $reports,
            $templates,
            (bool) $row->is_active,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function processPayload(EmissionProcess $process): array
    {
        return [
            'processCode' => $process->processCode,
            'menuProcessCode' => $process->menuProcessCode,
            'channels' => $process->channels,
            'allowsConsolidated' => $process->allowsConsolidated,
            'allowsSegmented' => $process->allowsSegmented,
            'modes' => [
                'consolidated' => $process->allowsConsolidated,
                'segmented' => $process->allowsSegmented,
            ],
            'requiresPreview' => $process->requiresPreview,
            'reports' => $process->reports,
            'mailTemplates' => $process->mailTemplates,
        ];
    }
}
