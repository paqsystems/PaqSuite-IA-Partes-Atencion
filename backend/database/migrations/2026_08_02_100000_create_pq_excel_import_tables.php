<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-14 importaciones Excel (TR-009). Runtime MONO: Query Builder vía SpExcelImportRepository.
 * SQL Server: desplegar también backend/database/sp/pq_sp_excel_core.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_excel_procesos')) {
            Schema::create('pq_excel_procesos', function (Blueprint $table) {
                $table->string('codigo', 64)->primary();
                $table->string('descripcion', 200);
                $table->string('menu_process_code', 64);
                $table->string('handler_class', 255)->nullable();
                $table->boolean('allow_partial')->default(false);
                $table->string('sheet_name', 64)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pq_excel_proceso_columnas')) {
            Schema::create('pq_excel_proceso_columnas', function (Blueprint $table) {
                $table->id();
                $table->string('proceso_codigo', 64);
                $table->string('column_key', 64);
                $table->string('header', 120);
                $table->string('caption_key', 120)->nullable();
                $table->string('data_type', 20)->default('string');
                $table->boolean('is_required')->default(false);
                $table->unsignedInteger('orden')->default(0);
                $table->timestamps();

                $table->foreign('proceso_codigo')->references('codigo')->on('pq_excel_procesos')->cascadeOnDelete();
                $table->unique(['proceso_codigo', 'column_key'], 'pq_excel_proceso_columnas_unique');
                $table->index(['proceso_codigo', 'orden'], 'pq_excel_proceso_columnas_orden_idx');
            });
        }

        if (! Schema::hasTable('pq_excel_batches')) {
            Schema::create('pq_excel_batches', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('process_code', 64);
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('created_by_user_id');
                $table->string('status', 24);
                $table->string('mode', 8)->nullable();
                $table->string('sheet_name', 64)->nullable();
                $table->string('original_file_name', 255)->nullable();
                $table->unsignedBigInteger('file_size_bytes')->default(0);
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('valid_rows')->default(0);
                $table->unsignedInteger('error_rows')->default(0);
                $table->unsignedInteger('processed_rows')->nullable();
                $table->unsignedInteger('failed_rows')->nullable();
                $table->text('result_payload_json')->nullable();
                $table->string('message_key', 128)->nullable();
                $table->dateTime('validated_at')->nullable();
                $table->dateTime('processed_at')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status'], 'pq_excel_batches_company_status_idx');
                $table->index(['process_code', 'created_at'], 'pq_excel_batches_process_idx');
                $table->index(['created_by_user_id', 'created_at'], 'pq_excel_batches_user_idx');
            });
        }

        if (! Schema::hasTable('pq_excel_batch_rows')) {
            Schema::create('pq_excel_batch_rows', function (Blueprint $table) {
                $table->id();
                $table->char('batch_id', 36);
                $table->unsignedInteger('row_number');
                $table->text('raw_json');
                $table->text('normalized_json')->nullable();
                $table->boolean('is_valid')->default(false);
                $table->timestamp('created_at')->nullable();

                $table->foreign('batch_id')->references('id')->on('pq_excel_batches')->cascadeOnDelete();
                $table->index(['batch_id', 'row_number'], 'pq_excel_batch_rows_idx');
            });
        }

        if (! Schema::hasTable('pq_excel_batch_row_errors')) {
            Schema::create('pq_excel_batch_row_errors', function (Blueprint $table) {
                $table->id();
                $table->char('batch_id', 36);
                $table->unsignedInteger('row_number');
                $table->string('column_key', 64);
                $table->string('message_key', 128);
                $table->text('params_json')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->foreign('batch_id')->references('id')->on('pq_excel_batches')->cascadeOnDelete();
                $table->index(['batch_id', 'row_number'], 'pq_excel_batch_row_errors_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_excel_batch_row_errors');
        Schema::dropIfExists('pq_excel_batch_rows');
        Schema::dropIfExists('pq_excel_batches');
        Schema::dropIfExists('pq_excel_proceso_columnas');
        Schema::dropIfExists('pq_excel_procesos');
    }
};
