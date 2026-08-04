<?php

namespace App\Services\ExcelImport;

use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportTaskDispatcher;

final class SyncNoopExcelImportTaskDispatcher implements ExcelImportTaskDispatcher
{
    public function dispatchSystemTask(
        string $processCode,
        array $parameters,
        int|string|null $companyId,
    ): string {
        return 'noop';
    }
}
