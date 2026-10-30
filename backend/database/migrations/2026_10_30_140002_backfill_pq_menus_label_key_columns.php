<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Precondición GEN-07 piloto (fw-sp-pq_menus_labelKey_not_null_pilot): sin NULL en label_key / labelKey.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        $query = DB::table('pq_menus')->select(['id', 'codigo', 'label_key']);

        if (Schema::hasColumn('pq_menus', 'label_key')) {
            $rows = (clone $query)
                ->where(function ($builder) {
                    $builder
                        ->whereNull('label_key')
                        ->orWhere('label_key', '');
                })
                ->get();

            foreach ($rows as $row) {
                DB::table('pq_menus')
                    ->where('id', $row->id)
                    ->update(['label_key' => 'menu.'.$row->codigo]);
            }
        }

        if (! Schema::hasColumn('pq_menus', 'labelKey')) {
            return;
        }

        $legacyRows = DB::table('pq_menus')
            ->select(['id', 'codigo', 'label_key', 'labelKey'])
            ->where(function ($builder) {
                $builder
                    ->whereNull('labelKey')
                    ->orWhere('labelKey', '');
            })
            ->get();

        foreach ($legacyRows as $row) {
            $labelKey = $row->label_key;
            if ($labelKey === null || $labelKey === '') {
                $labelKey = 'menu.'.$row->codigo;
            }

            DB::table('pq_menus')
                ->where('id', $row->id)
                ->update(['labelKey' => $labelKey]);
        }
    }

    public function down(): void
    {
        // Datos: no revertir backfill.
    }
};
