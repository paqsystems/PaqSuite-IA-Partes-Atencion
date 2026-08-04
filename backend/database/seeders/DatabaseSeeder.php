<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PqRolSeeder::class,
            PqMenuSeeder::class,
            PqPermisoSeeder::class,
            PqPartesTiposTareaSeeder::class,
            PqPartesUsuariosSeedSeeder::class,
            PqPartesParametrosSeeder::class,
            PqExcelImportSeeder::class,
        ]);
    }
}
