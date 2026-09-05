<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_empresa')) {
            Schema::create('pq_empresa', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pq_roles')) {
            Schema::create('pq_roles', function (Blueprint $table) {
                $table->id();
                $table->string('codigo')->unique();
                $table->string('nombre');
                $table->boolean('acceso_total')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pq_permisos')) {
            Schema::create('pq_permisos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('empresa_id')->constrained('pq_empresa')->cascadeOnDelete();
                $table->foreignId('rol_id')->constrained('pq_roles')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'empresa_id', 'rol_id']);
            });
        }

        if (! Schema::hasTable('pq_menus')) {
            Schema::create('pq_menus', function (Blueprint $table) {
                // id jerárquico (BASE 76 / SPEC-001-07 §1.2): no identity opaco.
                $table->unsignedBigInteger('id')->primary();
                $table->string('codigo')->unique();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('titulo');
                $table->string('ruta')->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->boolean('enabled')->default(true);
                $table->string('procedimiento')->nullable();
                $table->string('process_type', 8)->nullable();
                $table->string('icon_name')->nullable();
                $table->timestamps();

                // SQL Server no admite ON DELETE SET NULL en self-FK (ciclos/rutas en cascada).
                $table->foreign('parent_id')->references('id')->on('pq_menus');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_menus');
        Schema::dropIfExists('pq_permisos');
        Schema::dropIfExists('pq_roles');
        Schema::dropIfExists('pq_empresa');
    }
};
