<?php

use Database\Seeders\PqEmissionSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new PqEmissionSeeder())->seedPaqueteHorasCatalog();
    }

    public function down(): void
    {
        // Catálogo emisión: no revertir en down (mismo criterio que PqEmissionSeeder).
    }
};
