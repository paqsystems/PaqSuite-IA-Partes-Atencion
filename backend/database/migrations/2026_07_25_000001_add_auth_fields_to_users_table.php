<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('usuario')->nullable()->unique()->after('name');
            $table->boolean('first_login')->default(false)->after('password');
            $table->boolean('supervisor')->default(false)->after('first_login');
            $table->boolean('activo')->default(true)->after('supervisor');
            $table->boolean('inhabilitado')->default(false)->after('activo');
            $table->string('locale', 16)->nullable()->after('inhabilitado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['usuario', 'first_login', 'supervisor', 'activo', 'inhabilitado', 'locale']);
        });
    }
};
