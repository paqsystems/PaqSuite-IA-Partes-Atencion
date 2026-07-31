<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PqPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $rolId = DB::table('pq_roles')->where('codigo', 'SUPERVISOR')->value('id');
        $empresaId = DB::table('pq_empresa')->where('nombre', 'Partes Demo')->value('id');
        if ($rolId === null || $empresaId === null) {
            return;
        }

        $now = now()->format('Ymd H:i:s');
        $usuarios = User::query()
            ->whereIn('usuario', ['admin', 'PQ'])
            ->get();

        foreach ($usuarios as $user) {
            DB::table('pq_permisos')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'empresa_id' => $empresaId,
                    'rol_id' => $rolId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
