<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Database\Seeders\AuthParametrosSeeder;

class PqRolSeeder extends Seeder
{
    public function run(): void
    {
        AuthParametrosSeeder::seed(app(ParametroStore::class));

        $now = $this->sqlServerNow();

        $empresaExists = DB::table('pq_empresa')->where('nombre', 'Partes Demo')->exists();
        if (! $empresaExists) {
            DB::table('pq_empresa')->insert([
                'nombre' => 'Partes Demo',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('pq_empresa')->where('nombre', 'Partes Demo')->update([
                'activo' => true,
                'updated_at' => $now,
            ]);
        }

        DB::table('pq_roles')->updateOrInsert(
            ['codigo' => 'SUPERVISOR'],
            [
                'nombre' => 'Supervisor',
                'acceso_total' => true,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Seed canónico SPEC-001-18 / runbook install: admin + PQ (sin firstLogin obligatorio).
        $this->upsertUser('admin', [
            'name' => 'Administrador',
            'email' => 'admin@partes.local',
            'password' => Hash::make('Paqsystems'),
            'first_login' => false,
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
            'locale' => 'es',
            'open_in_new_tab' => false,
        ], $now);

        $this->upsertUser('PQ', [
            'name' => 'PaqSystems',
            'email' => 'pq@paqsystems.local',
            'password' => Hash::make('PaqSystems26*'),
            'first_login' => false,
            'supervisor' => true,
            'activo' => true,
            'inhabilitado' => false,
            'locale' => 'es',
            'open_in_new_tab' => false,
        ], $now);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertUser(string $usuario, array $attributes, string $now): void
    {
        $existing = DB::table('users')->where('usuario', $usuario)->exists();
        $payload = array_merge($attributes, [
            'usuario' => $usuario,
            'updated_at' => $now,
        ]);

        if ($existing) {
            DB::table('users')->where('usuario', $usuario)->update($payload);

            return;
        }

        $payload['created_at'] = $now;
        DB::table('users')->insert($payload);
    }

    private function sqlServerNow(): string
    {
        // Inequívoco en SQL Server con DATEFORMAT dmy (estilo 112).
        return now()->format('Ymd H:i:s');
    }
}
