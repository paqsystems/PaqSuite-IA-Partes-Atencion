<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-34: laravel-core 1.3.18-beta.30+ usa kind emissionDesignerSchema (22 chars);
 * la migration FW 000037 definió kind VARCHAR(16).
 */
return new class extends Migration
{
    private const KIND_LENGTH = 32;

    public function up(): void
    {
        if (! Schema::hasTable('pq_installation_run_step')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        $current = DB::scalar(
            "SELECT CHARACTER_MAXIMUM_LENGTH
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = 'dbo'
               AND TABLE_NAME = 'pq_installation_run_step'
               AND COLUMN_NAME = 'kind'"
        );

        if ($current !== null && (int) $current >= self::KIND_LENGTH) {
            return;
        }

        DB::statement(
            'ALTER TABLE dbo.pq_installation_run_step ALTER COLUMN kind NVARCHAR('.self::KIND_LENGTH.') NOT NULL'
        );
    }

    public function down(): void
    {
        // No revertir: podría truncar filas con emissionDesignerSchema.
    }
};
