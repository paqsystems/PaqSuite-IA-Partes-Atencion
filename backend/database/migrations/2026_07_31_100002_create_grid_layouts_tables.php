<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-11 layouts. Idempotente. En SQL Server se evitan cascadas múltiples
 * (user_id + layout_id → users) usando FK sin ON DELETE CASCADE en last_used.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_grid_layouts')) {
            Schema::create('pq_grid_layouts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('proceso');
                $table->string('grid_id');
                $table->string('layout_name');
                $table->longText('state_json');
                $table->boolean('is_system')->default(false);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
                $table->unique(['user_id', 'proceso', 'grid_id', 'layout_name'], 'pq_grid_layouts_unique');
            });
        }

        if (! Schema::hasTable('pq_grid_layout_last_used')) {
            Schema::create('pq_grid_layout_last_used', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('proceso');
                $table->string('grid_id');
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('layout_id')->references('id')->on('pq_grid_layouts');
                $table->unique(['user_id', 'proceso', 'grid_id'], 'pq_grid_layout_last_used_unique');
            });
        }
    }

    public function down(): void
    {
        // No drop: puede haber datos de layouts de usuario.
    }
};
