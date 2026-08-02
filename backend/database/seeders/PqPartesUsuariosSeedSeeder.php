<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seed dominio Partes: admin y PQ como asistentes supervisores (TR-002).
 */
class PqPartesUsuariosSeedSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('PQ_PARTES_USUARIOS')) {
            return;
        }

        $now = now()->format('Ymd H:i:s');

        foreach (['admin', 'PQ'] as $usuario) {
            $user = DB::table('users')->where('usuario', $usuario)->first();
            if ($user === null) {
                continue;
            }

            $existing = DB::table('PQ_PARTES_USUARIOS')->where('user_id', $user->id)->first();
            $payload = [
                'code' => $usuario,
                'nombre' => (string) ($user->name ?? $usuario),
                'email' => $user->email,
                'supervisor' => true,
                'activo' => true,
                'inhabilitado' => false,
                'updated_at' => $now,
            ];

            if ($existing === null) {
                DB::table('PQ_PARTES_USUARIOS')->insert(array_merge($payload, [
                    'user_id' => $user->id,
                    'created_at' => $now,
                ]));

                continue;
            }

            DB::table('PQ_PARTES_USUARIOS')->where('id', $existing->id)->update($payload);
        }
    }
}
