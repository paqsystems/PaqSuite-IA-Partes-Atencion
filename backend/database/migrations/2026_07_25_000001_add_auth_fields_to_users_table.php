<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'usuario')) {
                $table->string('usuario')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'first_login')) {
                $table->boolean('first_login')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'supervisor')) {
                $table->boolean('supervisor')->default(false)->after('first_login');
            }
            if (! Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('supervisor');
            }
            if (! Schema::hasColumn('users', 'inhabilitado')) {
                $table->boolean('inhabilitado')->default(false)->after('activo');
            }
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 16)->nullable()->after('inhabilitado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $drops = [];
            foreach (['usuario', 'first_login', 'supervisor', 'activo', 'inhabilitado', 'locale'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $drops[] = $column;
                }
            }
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
