<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Metadatos de plantilla GEN-14 (patrón PedidosWeb GEN-07):
 * help_text / decimal_places en columnas; boolean_format_plantilla en proceso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pq_excel_proceso_columnas', function (Blueprint $table) {
            if (! Schema::hasColumn('pq_excel_proceso_columnas', 'help_text')) {
                $table->string('help_text', 500)->nullable()->after('is_required');
            }
            if (! Schema::hasColumn('pq_excel_proceso_columnas', 'decimal_places')) {
                $table->unsignedTinyInteger('decimal_places')->nullable()->after('help_text');
            }
        });

        Schema::table('pq_excel_procesos', function (Blueprint $table) {
            if (! Schema::hasColumn('pq_excel_procesos', 'boolean_format_plantilla')) {
                $table->string('boolean_format_plantilla', 32)
                    ->default('VERDADERO_FALSO')
                    ->after('sheet_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pq_excel_proceso_columnas', function (Blueprint $table) {
            if (Schema::hasColumn('pq_excel_proceso_columnas', 'decimal_places')) {
                $table->dropColumn('decimal_places');
            }
            if (Schema::hasColumn('pq_excel_proceso_columnas', 'help_text')) {
                $table->dropColumn('help_text');
            }
        });
        Schema::table('pq_excel_procesos', function (Blueprint $table) {
            if (Schema::hasColumn('pq_excel_procesos', 'boolean_format_plantilla')) {
                $table->dropColumn('boolean_format_plantilla');
            }
        });
    }
};
