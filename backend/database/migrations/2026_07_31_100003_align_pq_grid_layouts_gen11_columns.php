<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea el esquema legacy de pq_grid_layouts (layout_data / is_default)
 * al contrato GEN-11 (state_json / is_system) usado por GridLayoutsController.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_grid_layouts')) {
            return;
        }

        if (Schema::hasColumn('pq_grid_layouts', 'layout_data') && ! Schema::hasColumn('pq_grid_layouts', 'state_json')) {
            DB::statement('EXEC sp_rename \'pq_grid_layouts.layout_data\', \'state_json\', \'COLUMN\'');
        } elseif (! Schema::hasColumn('pq_grid_layouts', 'state_json')) {
            DB::statement('ALTER TABLE pq_grid_layouts ADD state_json NVARCHAR(MAX) NULL');
        }

        if (Schema::hasColumn('pq_grid_layouts', 'is_default') && ! Schema::hasColumn('pq_grid_layouts', 'is_system')) {
            DB::statement('EXEC sp_rename \'pq_grid_layouts.is_default\', \'is_system\', \'COLUMN\'');
        } elseif (! Schema::hasColumn('pq_grid_layouts', 'is_system')) {
            DB::statement('ALTER TABLE pq_grid_layouts ADD is_system BIT NOT NULL CONSTRAINT DF_pq_grid_layouts_is_system DEFAULT 0');
        }

        if (Schema::hasColumn('pq_grid_layouts', 'is_system')) {
            DB::statement('UPDATE pq_grid_layouts SET is_system = 0 WHERE is_system IS NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_grid_layouts')) {
            return;
        }

        if (Schema::hasColumn('pq_grid_layouts', 'state_json') && ! Schema::hasColumn('pq_grid_layouts', 'layout_data')) {
            DB::statement('EXEC sp_rename \'pq_grid_layouts.state_json\', \'layout_data\', \'COLUMN\'');
        }

        if (Schema::hasColumn('pq_grid_layouts', 'is_system') && ! Schema::hasColumn('pq_grid_layouts', 'is_default')) {
            DB::statement('EXEC sp_rename \'pq_grid_layouts.is_system\', \'is_default\', \'COLUMN\'');
        }
    }
};
