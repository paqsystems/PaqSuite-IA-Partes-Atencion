<?php

namespace App\Services\ExcelImport;

use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportNotificationPort;

final class NullExcelImportNotificationPort implements ExcelImportNotificationPort
{
    public function notify(
        int|string $userId,
        string $titleKey,
        string $bodyKey,
        array $params,
        string $batchId,
    ): void {
    }
}
