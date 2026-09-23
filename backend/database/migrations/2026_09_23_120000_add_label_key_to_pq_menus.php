<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        if (! Schema::hasColumn('pq_menus', 'label_key')) {
            Schema::table('pq_menus', function (Blueprint $table) {
                $table->string('label_key')->nullable()->after('titulo');
            });
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlsrv') {
            DB::unprepared(
                "UPDATE dbo.pq_menus SET label_key = CONCAT('menu.', codigo) "
                ."WHERE label_key IS NULL OR LTRIM(RTRIM(label_key)) = ''"
            );
        } else {
            DB::table('pq_menus')
                ->whereNull('label_key')
                ->orWhere('label_key', '')
                ->update(['label_key' => DB::raw("CONCAT('menu.', codigo)")]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pq_menus') && Schema::hasColumn('pq_menus', 'label_key')) {
            Schema::table('pq_menus', function (Blueprint $table) {
                $table->dropColumn('label_key');
            });
        }
    }
};
