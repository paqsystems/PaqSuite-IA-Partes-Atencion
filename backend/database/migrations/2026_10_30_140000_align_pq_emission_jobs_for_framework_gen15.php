<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea pq_emission_jobs al contrato SP GEN-15 de laravel-core 1.3.18-beta.12+.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_emission_jobs')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            $adds = [
                'notification_id' => 'CHAR(36) NULL',
                'segment_key' => 'NVARCHAR(128) NULL',
                'communication_intents_json' => 'NVARCHAR(MAX) NULL',
                'artifact_path' => 'NVARCHAR(1024) NULL',
            ];
            foreach ($adds as $column => $definition) {
                DB::statement(
                    "IF COL_LENGTH(N'dbo.pq_emission_jobs', N'{$column}') IS NULL
                     BEGIN
                         ALTER TABLE dbo.pq_emission_jobs ADD {$column} {$definition};
                     END"
                );
            }
        } else {
            Schema::table('pq_emission_jobs', function (Blueprint $table) {
                if (! Schema::hasColumn('pq_emission_jobs', 'notification_id')) {
                    $table->char('notification_id', 36)->nullable();
                }
                if (! Schema::hasColumn('pq_emission_jobs', 'segment_key')) {
                    $table->string('segment_key', 128)->nullable();
                }
                if (! Schema::hasColumn('pq_emission_jobs', 'communication_intents_json')) {
                    $table->text('communication_intents_json')->nullable();
                }
                if (! Schema::hasColumn('pq_emission_jobs', 'artifact_path')) {
                    $table->string('artifact_path', 1024)->nullable();
                }
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        $widen = [
            'status' => 24,
            'mode' => 20,
            'channel' => 20,
        ];

        foreach ($widen as $column => $length) {
            DB::statement(
                "IF COL_LENGTH(N'dbo.pq_emission_jobs', N'{$column}') IS NOT NULL
                 BEGIN
                     ALTER TABLE dbo.pq_emission_jobs ALTER COLUMN {$column} NVARCHAR({$length}) NOT NULL;
                 END"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_emission_jobs')) {
            return;
        }

        Schema::table('pq_emission_jobs', function (Blueprint $table) {
            foreach (['notification_id', 'segment_key', 'communication_intents_json', 'artifact_path'] as $column) {
                if (Schema::hasColumn('pq_emission_jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
