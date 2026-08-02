<?php

namespace App\Services\ExcelImport;

use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportAuditPort;

final class NullExcelImportAuditPort implements ExcelImportAuditPort
{
    public function record(
        string $action,
        string $result,
        string $batchId,
        int|string|null $companyId,
        int|string|null $userId,
        array $payload,
    ): void {
    }
}
