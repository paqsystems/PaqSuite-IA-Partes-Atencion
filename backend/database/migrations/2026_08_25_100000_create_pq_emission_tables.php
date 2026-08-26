<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-15 emisiones (TR-011). Runtime MONO: Query Builder vía SpEmissionRepository.
 * SQL Server: desplegar también backend/database/sp/pq_sp_emission_core.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_emission_processes')) {
            Schema::create('pq_emission_processes', function (Blueprint $table) {
                $table->string('process_code', 64)->primary();
                $table->string('name', 200);
                $table->string('description', 400)->nullable();
                $table->string('menu_process_code', 64);
                $table->boolean('permite_consolidado')->default(true);
                $table->boolean('permite_segmentado')->default(false);
                $table->boolean('requiere_vista_previa')->default(false);
                $table->boolean('canal_pdf')->default(true);
                $table->boolean('canal_print')->default(false);
                $table->boolean('canal_excel')->default(false);
                $table->boolean('canal_csv')->default(false);
                $table->boolean('canal_mail')->default(false);
                $table->boolean('canal_zip')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pq_emission_reports')) {
            Schema::create('pq_emission_reports', function (Blueprint $table) {
                $table->id();
                $table->string('process_code', 64);
                $table->string('report_code', 64);
                $table->string('name', 200);
                $table->boolean('is_standard')->default(true);
                $table->boolean('is_principal')->default(false);
                $table->boolean('visible_mobile')->default(true);
                $table->text('layout_definition')->nullable();
                $table->string('layout_mime', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->foreign('process_code')->references('process_code')->on('pq_emission_processes')->cascadeOnDelete();
                $table->unique(['process_code', 'report_code'], 'pq_emission_reports_unique');
            });
        }

        if (! Schema::hasTable('pq_emission_mail_templates')) {
            Schema::create('pq_emission_mail_templates', function (Blueprint $table) {
                $table->id();
                $table->string('process_code', 64);
                $table->string('template_code', 64);
                $table->string('name', 200);
                $table->string('subject_template', 400)->nullable();
                $table->text('body_html_template')->nullable();
                $table->boolean('is_principal')->default(false);
                $table->boolean('is_standard')->default(true);
                $table->boolean('visible_mobile')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->foreign('process_code')->references('process_code')->on('pq_emission_processes')->cascadeOnDelete();
                $table->unique(['process_code', 'template_code'], 'pq_emission_mail_templates_unique');
            });
        }

        if (! Schema::hasTable('pq_emission_jobs')) {
            Schema::create('pq_emission_jobs', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('process_code', 64);
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('group_id', 64)->nullable();
                $table->unsignedBigInteger('created_by_user_id');
                $table->string('status', 16);
                $table->string('mode', 16);
                $table->string('channel', 16);
                $table->unsignedBigInteger('report_id')->nullable();
                $table->unsignedBigInteger('mail_template_id')->nullable();
                $table->unsignedInteger('dataset_row_count')->default(0);
                $table->unsignedBigInteger('estimated_size_bytes')->default(0);
                $table->string('artifact_file_name', 255)->nullable();
                $table->string('artifact_mime', 128)->nullable();
                $table->string('result_message_key', 128)->nullable();
                $table->char('preview_session_id', 36)->nullable();
                $table->dateTime('finished_at')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'status'], 'pq_emission_jobs_company_status_idx');
            });
        }

        if (! Schema::hasTable('pq_emission_artifacts')) {
            Schema::create('pq_emission_artifacts', function (Blueprint $table) {
                $table->id();
                $table->char('job_id', 36);
                $table->string('file_name', 255);
                $table->string('mime_type', 128);
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->longText('content_base64')->nullable();
                $table->timestamps();
                $table->foreign('job_id')->references('id')->on('pq_emission_jobs')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('pq_emission_preview_sessions')) {
            Schema::create('pq_emission_preview_sessions', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('process_code', 64);
                $table->unsignedBigInteger('report_id')->nullable();
                $table->string('mode', 16);
                $table->string('channel', 16);
                $table->string('group_id', 64)->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->dateTime('expires_at');
                $table->longText('artifact_content_base64')->nullable();
                $table->string('artifact_file_name', 255)->nullable();
                $table->string('artifact_mime', 128)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_emission_preview_sessions');
        Schema::dropIfExists('pq_emission_artifacts');
        Schema::dropIfExists('pq_emission_jobs');
        Schema::dropIfExists('pq_emission_mail_templates');
        Schema::dropIfExists('pq_emission_reports');
        Schema::dropIfExists('pq_emission_processes');
    }
};
