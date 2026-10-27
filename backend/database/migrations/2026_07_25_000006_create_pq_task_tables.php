<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-13 tareas programadas (host). Producción: acceso vía stored procedures
 * (pq_sp_task_process_*, pq_sp_task_definition_*, pq_sp_task_execution_*);
 * estas tablas son el adapter Eloquent de referencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_task_process', function (Blueprint $table) {
            $table->id('process_id');
            $table->string('process_code', 64)->unique();
            $table->string('process_name', 200);
            $table->string('module_code', 64)->nullable();
            $table->string('handler_class', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_manual_execution')->default(true);
            $table->boolean('supports_scheduled_execution')->default(true);
            $table->boolean('supports_simulation')->default(false);
            $table->boolean('works_by_company')->default(true);
            $table->string('selection_strategy_auto', 64)->nullable();
            $table->string('selection_strategy_manual', 64)->nullable();
            $table->string('idempotency_strategy', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pq_task_process_parameter', function (Blueprint $table) {
            $table->id('process_parameter_id');
            $table->unsignedBigInteger('process_id');
            $table->string('parameter_code', 64);
            $table->string('parameter_name', 200);
            $table->string('parameter_description', 400)->nullable();
            $table->string('data_type', 16)->default('STRING');
            $table->string('ui_control_type', 32)->nullable();
            $table->boolean('is_required')->default(false);
            $table->text('default_value_text')->nullable();
            $table->string('allowed_values_source', 128)->nullable();
            $table->string('validation_rule', 255)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_user_editable')->default(true);
            $table->boolean('is_multivalue')->default(false);
            $table->string('example_value', 255)->nullable();
            $table->text('metadata_json')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('pq_task_process')->cascadeOnDelete();
            $table->unique(['process_id', 'parameter_code'], 'pq_task_process_parameter_unique');
        });

        Schema::create('pq_task_definition', function (Blueprint $table) {
            $table->id('task_definition_id');
            $table->unsignedBigInteger('process_id');
            $table->string('task_code', 64)->nullable()->unique();
            $table->string('task_title', 200);
            $table->string('task_description', 400)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('frequency_type', 32);
            $table->unsignedInteger('frequency_interval')->nullable();
            $table->string('frequency_days_mask', 32)->nullable();
            $table->date('base_date')->nullable();
            $table->string('base_time', 8)->nullable();
            $table->dateTime('next_run_at')->nullable();
            $table->dateTime('last_run_at')->nullable();
            $table->string('last_status_code', 32)->nullable();
            $table->boolean('notify_enabled')->default(false);
            $table->boolean('notify_on_error')->default(true);
            $table->boolean('notify_on_success')->default(false);
            $table->text('notify_emails')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('pq_task_process')->cascadeOnDelete();
        });

        Schema::create('pq_task_definition_company', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_definition_id');
            $table->unsignedBigInteger('company_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('task_definition_id')->references('task_definition_id')->on('pq_task_definition')->cascadeOnDelete();
            $table->unique(['task_definition_id', 'company_id'], 'pq_task_definition_company_unique');
        });

        Schema::create('pq_task_definition_parameter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_definition_id');
            $table->string('parameter_code', 64);
            $table->string('value_string', 400)->nullable();
            $table->integer('value_int')->nullable();
            $table->decimal('value_decimal', 18, 6)->nullable();
            $table->boolean('value_bool')->nullable();
            $table->dateTime('value_datetime')->nullable();
            $table->text('value_json')->nullable();
            $table->timestamps();

            $table->foreign('task_definition_id')->references('task_definition_id')->on('pq_task_definition')->cascadeOnDelete();
            $table->unique(['task_definition_id', 'parameter_code'], 'pq_task_definition_parameter_unique');
        });

        Schema::create('pq_task_execution_batch', function (Blueprint $table) {
            $table->id('batch_id');
            $table->unsignedBigInteger('task_definition_id');
            $table->string('mode', 16);
            $table->boolean('is_simulation')->default(false);
            $table->dateTime('triggered_at');
            $table->timestamps();

            $table->foreign('task_definition_id')->references('task_definition_id')->on('pq_task_definition')->cascadeOnDelete();
        });

        Schema::create('pq_task_execution', function (Blueprint $table) {
            $table->id('execution_id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('task_definition_id');
            $table->unsignedBigInteger('company_id');
            $table->string('mode', 16);
            $table->boolean('is_simulation')->default(false);
            $table->string('status_code', 32);
            $table->boolean('is_overlapped_skip')->default(false);
            $table->string('message', 400)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('batch_id')->on('pq_task_execution_batch')->cascadeOnDelete();
            $table->index(['task_definition_id', 'company_id', 'status_code'], 'pq_task_execution_overlap_idx');
        });

        Schema::create('pq_task_execution_parameter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('execution_id');
            $table->string('parameter_code', 64);
            $table->text('value_json')->nullable();
            $table->timestamps();

            $table->foreign('execution_id')->references('execution_id')->on('pq_task_execution')->cascadeOnDelete();
            $table->unique(['execution_id', 'parameter_code'], 'pq_task_execution_parameter_unique');
        });

        Schema::create('pq_task_execution_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('execution_id');
            $table->string('level', 16);
            $table->text('message');
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->foreign('execution_id')->references('execution_id')->on('pq_task_execution')->cascadeOnDelete();
            $table->index(['execution_id'], 'pq_task_execution_log_execution_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_task_execution_log');
        Schema::dropIfExists('pq_task_execution_parameter');
        Schema::dropIfExists('pq_task_execution');
        Schema::dropIfExists('pq_task_execution_batch');
        Schema::dropIfExists('pq_task_definition_parameter');
        Schema::dropIfExists('pq_task_definition_company');
        Schema::dropIfExists('pq_task_definition');
        Schema::dropIfExists('pq_task_process_parameter');
        Schema::dropIfExists('pq_task_process');
    }
};
