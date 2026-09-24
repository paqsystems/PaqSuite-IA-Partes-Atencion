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

        $menus = DB::table('pq_menus')
            ->select(['id', 'codigo'])
            ->where(function ($query) {
                $query
                    ->whereNull('label_key')
                    ->orWhere('label_key', '');
            })
            ->get();

        foreach ($menus as $menu) {
            DB::table('pq_menus')
                ->where('id', $menu->id)
                ->update(['label_key' => 'menu.' . $menu->codigo]);
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
