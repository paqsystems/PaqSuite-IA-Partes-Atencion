<?php

namespace App\Tasks\Backup;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tasks\Backup\MonoRdsBackupGateway;

final class RdsSqlServerBackupGateway implements MonoRdsBackupGateway
{
    public function startExport(
        string $databaseName,
        string $s3Arn,
        string $filePrefix,
        bool $overwrite = true,
    ): array
    {
        $destinationArn = rtrim($s3Arn, '/').'/'.$this->normalizeFileName($filePrefix);
        $rows = DB::connection()->select(
            'EXEC msdb.dbo.rds_backup_database
                @source_db_name = ?,
                @s3_arn_to_backup_to = ?,
                @overwrite_s3_backup_file = ?',
            [$databaseName, $destinationArn, $overwrite ? 1 : 0],
        );

        $row = isset($rows[0]) ? (array) $rows[0] : [];
        $taskId = (string) ($row['task_id'] ?? $row['taskId'] ?? '');

        if ($taskId === '') {
            throw new \RuntimeException('RDS no devolvió task_id al iniciar el backup.');
        }

        return ['taskId' => $taskId];
    }

    public function getTaskStatus(string $taskId): array
    {
        $rows = DB::connection()->select(
            'EXEC msdb.dbo.rds_task_status @task_id = ?',
            [$taskId],
        );
        $row = isset($rows[0]) ? (array) $rows[0] : [];
        $lifecycle = strtoupper((string) ($row['lifecycle'] ?? ''));

        return [
            'status' => match ($lifecycle) {
                'SUCCESS' => 'success',
                'ERROR', 'FAILED', 'CANCELED', 'CANCELLED' => 'error',
                default => 'running',
            },
            'percent' => isset($row['% complete']) ? (int) $row['% complete'] : null,
            'message' => $row['task_info'] ?? null,
        ];
    }

    private function normalizeFileName(string $filePrefix): string
    {
        $fileName = trim($filePrefix);
        if ($fileName === '') {
            $fileName = 'backup';
        }

        return str_ends_with(strtolower($fileName), '.bak')
            ? $fileName
            : $fileName.'.bak';
    }
}
